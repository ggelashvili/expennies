<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataObjects\UserProfileData;
use App\Entity\User;

interface UserProfileServiceInterface
{
    public function update(User $user, UserProfileData $data): void;

    public function get(User $user);
}