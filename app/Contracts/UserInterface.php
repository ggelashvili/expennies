<?php

declare(strict_types = 1);

namespace App\Contracts;

interface UserInterface
{
    public function getId(): int;
    public function getPassword(): string;
    public function setVerifiedAt(\DateTime $verifiedAt): static;
    public function hasTwoFactorAuthEnabled(): bool;
}
