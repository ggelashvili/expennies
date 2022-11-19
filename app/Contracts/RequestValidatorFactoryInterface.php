<?php

declare(strict_types = 1);

namespace App\Contracts;

interface RequestValidatorFactoryInterface
{
    public function make(string $class): RequestValidatorInterface;
}
