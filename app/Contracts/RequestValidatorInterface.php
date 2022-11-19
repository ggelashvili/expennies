<?php

declare(strict_types = 1);

namespace App\Contracts;

interface RequestValidatorInterface
{
    public function validate(array $data): array;
}
