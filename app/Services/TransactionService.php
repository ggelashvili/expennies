<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\DataTableQueryParams;
use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function create(TransactionData $transactionData, User $user): Transaction
    {
        $transaction = new Transaction();

        $transaction->setUser($user);

        return $this->update($transaction, $transactionData);
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c', 'r')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['description', 'amount', 'date', 'category'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('t.description LIKE :description')
                  ->setParameter('description', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy('t.' . $orderBy, $orderDir);
        }

        return new Paginator($query);
    }

    public function getById(int $id): ?Transaction
    {
        return $this->entityManager->find(Transaction::class, $id);
    }

    public function update(Transaction $transaction, TransactionData $transactionData): Transaction
    {
        $transaction->setDescription($transactionData->description);
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);

        return $transaction;
    }

    public function toggleReviewed(Transaction $transaction): void
    {
        $transaction->setReviewed(! $transaction->wasReviewed());
    }

    public function getTotals(\DateTime $startDate, \DateTime $endDate): array
    {
        $query = $this->entityManager->createQuery(
            'SELECT SUM(t.amount) AS net, 
                    SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) AS income,
                    SUM(CASE WHEN t.amount < 0 THEN ABS(t.amount) ELSE 0 END) as expense
             FROM App\Entity\Transaction t
             WHERE t.date BETWEEN :start AND :end'
        );

        $query->setParameter('start', $startDate->format('Y-m-d 00:00:00'));
        $query->setParameter('end', $endDate->format('Y-m-d 23:59:59'));

        return $query->getSingleResult();
    }

    public function getRecentTransactions(int $limit): array
    {
        return $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.category', 'c')
            ->orderBy('t.date', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    public function getMonthlySummary(int $year): array
    {
        $query = $this->entityManager->createQuery(
            'SELECT SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) as income,
                    SUM(CASE WHEN t.amount < 0 THEN abs(t.amount) ELSE 0 END) as expense, 
                    MONTH(t.date) as m
             FROM App\Entity\Transaction t 
             WHERE YEAR(t.date) = :year 
             GROUP BY m 
             ORDER BY m ASC'
        );

        $query->setParameter('year', $year);

        return $query->getArrayResult();
    }
}
