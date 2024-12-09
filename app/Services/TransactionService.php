<?php

declare(strict_types=1);


namespace App\Services;

use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

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
}