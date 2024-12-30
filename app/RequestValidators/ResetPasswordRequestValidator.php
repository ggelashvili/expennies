<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorInterface;
use App\Entity\User;
use App\Exception\ValidationException;
use Valitron\Validator;

class ResetPasswordRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['password', 'confirmPassword'])->message('Required field');;
        $v->rule('equals', 'password', 'confirmPassword');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
