<?php

declare(strict_types = 1);

namespace App\Services;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class CategoryService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(string $name, User $user): Category
    {
        $category = new Category();

        $category->setName($name);
        $category->setUser($user);

        $this->entityManager->flush();

        return $category;
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }
}
