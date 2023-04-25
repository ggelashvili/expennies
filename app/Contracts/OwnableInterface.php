<?php

declare(strict_types = 1);

namespace App\Contracts;

interface OwnableInterface
{
    public function getUser(): UserInterface;
}
