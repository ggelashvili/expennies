<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\UserProfileServiceInterface;
use App\DataObjects\UserProfileData;
use App\Entity\User;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function update(User $user, UserProfileData $data): void
    {
        $user->setName($data->name);
        $user->setTwoFactor($data->twoFactor);

        $this->entityManagerService->sync($user);
    }

    public function get(User $user): UserProfileData
    {
        $user = $this->entityManagerService->find(User::class, $user->getId());

        return new UserProfileData(
            $user->getEmail(),
            $user->getName(),
            $user->hasTwoFactorAuthEnabled()
        );
    }
}
