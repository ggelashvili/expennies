<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
class EntityManagerService implements EntityManagerServiceInterface
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->entityManager, $name)) {
            return call_user_func_array([$this->entityManager, $name], $arguments);
        }

        throw new \BadMethodCallException('Call to undefined method "' . $name . '"');
    }

    public function sync($entity = null): void
    {
        if ($entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function delete($entity, bool $sync = false): void
    {
        $this->entityManager->remove($entity);

        if ($sync) {
            $this->sync();
        }
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

    public function enableUserAuthFilter(int $userId): void
    {
        $this->getFilters()->enable('user')->setParameter('user_id', $userId);
    }
}
