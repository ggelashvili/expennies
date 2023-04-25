<?php

declare(strict_types = 1);

namespace App\Contracts;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
interface EntityManagerServiceInterface
{
    public function __call(string $name, array $arguments);

    public function sync($entity = null): void;

    public function delete($entity, bool $sync = false): void;

    public function clear(?string $entityName = null): void;

    public function enableUserAuthFilter(int $userId): void;
}
