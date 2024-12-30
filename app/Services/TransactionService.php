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

    public function toggleReviewed(Transaction $transaction): Transaction
    {
        return $transaction->setWasReviewed(! $transaction->wasReviewed());
    }

    public function getTotals(\DateTime $startDate, \DateTime $endDate): array
    {
        $income = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.date BETWEEN :startDate AND :endDate')
            ->andWhere('t.amount > 0')
            ->setParameters(
                [
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ]
            )
            ->getQuery()
            ->getSingleScalarResult();

        $expense = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('SUM(ABS(t.amount))')
            ->where('t.date BETWEEN :startDate AND :endDate')
            ->andWhere('t.amount < 0')
            ->setParameters(
                [
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ]
            )
            ->getQuery()
            ->getSingleScalarResult();

        return ['net' => ($income - $expense), 'income' => $income, 'expense' => $expense];
    }

    public function getRecentTransactions(int $limit): array
    {
        return $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.category', 'c')
            ->select('t.description as description', 't.amount as amount', 'c.name as category', 't.date as date')
            ->orderBy('t.date', 'DESC')
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