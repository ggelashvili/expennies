<?php

declare(strict_types=1);


namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(TransactionData $transactionData, User $user)
    {
        $transaction = new Transaction();
        
        $transaction->setUser($user);
        
        return $this->update($transaction, $transactionData);

    }
    public function getById(int $id): Transaction
    {
        return $this->entityManager->find(Transaction::class, $id);
    }

    public function update(Transaction $transaction, $transactionData): Transaction
    {

        $transaction->setDescription($transactionData->description);
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }

    public function delete(int $id): void
    {
        $transaction = $this->entityManager->find(Transaction::class, $id);

        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }

    public function getPaginatedTransaction(DataTableQueryParams $params)
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['description', 'amount', 'date'])
            ? $params->orderBy
            : 'date';

        $sortDir = strtolower($params->orderDir) === 'asc'?'asc':'desc';

        if (! empty($params->searchTerm)){
            $query->where('t.description LIKE :description')
                ->setParameter('description', '%'. addcslashes($params->searchTerm, '%_').'%');
        }

        $query->orderBy('t.'.$orderBy, $sortDir);

        return new Paginator($query);
    }

}