<?php

namespace App\Contracts;

interface RequestValidatorInterface
{
    public function validate(array $data): array;
}
