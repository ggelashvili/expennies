<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entity\Receipt;

class ReceiptService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function create($transaction, string $filename, string $storageFilename, string $mediaType): Receipt
    {
        $receipt = new Receipt();

        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setMediaType($mediaType);
        $receipt->setCreatedAt(new \DateTime());

        return $receipt;
    }

    public function getById(int $id)
    {
        return $this->entityManager->find(Receipt::class, $id);
    }
}
