<?php

declare(strict_types = 1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerService
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function clear(?string $entityName = null): void
    {
        if ($entityName === null) {
            $this->entityManager->clear();

            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entities = $unitOfWork->getIdentityMap()[$entityName] ?? [];

        foreach ($entities as $entity) {
            $this->entityManager->detach($entity);
        }
    }
}
