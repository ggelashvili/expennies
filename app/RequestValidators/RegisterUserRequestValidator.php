<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorInterface;
use App\Entity\User;
use App\Exception\ValidationException;
use Valitron\Validator;

class RegisterUserRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['name', 'email', 'password', 'confirmPassword']);
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password')->label('Confirm Password');
        $v->rule(
            fn($field, $value, $params, $fields) => ! $this->entityManager->getRepository(User::class)->count(
                ['email' => $value]
            ),
            'email'
        )->message('User with the given email address already exists');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
