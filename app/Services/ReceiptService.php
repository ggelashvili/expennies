<?php

declare(strict_types = 1);

namespace App\Services;

use App\Entity\Receipt;
use Doctrine\ORM\EntityManager;

class ReceiptService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create($transaction, string $filename, string $storageFilename): Receipt
    {
        $receipt = new Receipt();

        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setCreatedAt(new \DateTime());

        $this->entityManager->persist($receipt);
        $this->entityManager->flush();

        return $receipt;
    }
}
