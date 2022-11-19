<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\DataObjects\RegisterUserData;

interface AuthInterface
{
    public function user(): ?UserInterface;

    public function attemptLogin(array $credentials): bool;

    public function checkCredentials(UserInterface $user, array $credentials): bool;

    public function logOut(): void;

    public function register(RegisterUserData $data): UserInterface;

    public function logIn(UserInterface $user): void;
}
