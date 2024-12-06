<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Contracts\UserInterface;
use App\Entity\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table('users')]
#[HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column]
    private string $email;

    #[Column]
    private string $password;

    #[OneToMany(mappedBy: 'user', targetEntity: Category::class)]
    private Collection $categories;

    #[OneToMany(mappedBy: 'user', targetEntity: Transaction::class)]
    private Collection $transactions;

    public function __construct()
    {
        $this->categories   = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getCategories(): ArrayCollection|Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): User
    {
        $this->categories->add($category);

        return $this;
    }

    public function getTransactions(): ArrayCollection|Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): User
    {
        $this->transactions->add($transaction);

        return $this;
    }
}
