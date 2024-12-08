<?php

declare(strict_types=1);


namespace App\Services;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(mixed $name, mixed $getAttribute)
    {
        $transaction = new Transaction();

    }
}