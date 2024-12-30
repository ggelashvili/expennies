<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\RegisterUserData;
use App\Entity\User;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
        private readonly HashService $hashService,
    ) {
    }

    public function getById(int $id): ?UserInterface
    {
        return $this->entityManagerService->getRepository(User::class)->find($id);
    }

    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->entityManagerService->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $user->setEmail($data->email);
        $user->setPassword($this->hashService->hashPassword($data->password));
        $user->setName($data->name);

        $this->entityManagerService->sync($user);

        return $user;
    }

    public function verifyUser(UserInterface $user): void
    {
        $user->setVerifiedAt(new \DateTime());

        $this->entityManagerService->sync($user);
    }

    public function updatePassword(UserInterface $user, string $password): void
    {
        $user->setPassword($this->hashService->hashPassword($password));

        $this->entityManagerService->sync($user);
    }
}
