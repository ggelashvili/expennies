<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataObjects\RegisterUserData;
use App\Enum\AuthAttemptStatus;

interface AuthInterface
{
    public function user(): ?UserInterface;

    public function attemptLogin(array $credentials): AuthAttemptStatus;

    public function checkCredentials(UserInterface $user, array $credentials): bool;

    public function logout(): void;

    public function register(RegisterUserData $data): UserInterface;

    public function logIn(UserInterface $user): void;

    public function attemptTwoFactorLogin(array $data): bool;
}
