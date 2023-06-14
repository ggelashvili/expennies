<?php

declare(strict_types = 1);

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\RegisterUserData;
use App\Enum\AuthAttemptStatus;
use App\Mail\SignupEmail;
use App\Mail\TwoFactorAuthEmail;
use App\Services\UserLoginCodeService;

class Auth implements AuthInterface
{
    private ?UserInterface $user = null;

    public function __construct(
        private readonly UserProviderServiceInterface $userProvider,
        private readonly SessionInterface $session,
        private readonly SignupEmail $signupEmail,
        private readonly TwoFactorAuthEmail $twoFactorAuthEmail,
        private readonly UserLoginCodeService $userLoginCodeService
    ) {
    }

    public function user(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->session->get('user');

        if (! $userId) {
            return null;
        }

        $user = $this->userProvider->getById($userId);

        if (! $user) {
            return null;
        }

        $this->user = $user;

        return $this->user;
    }

    public function attemptLogin(array $credentials): AuthAttemptStatus
    {
        $user = $this->userProvider->getByCredentials($credentials);

        if (! $user || ! $this->checkCredentials($user, $credentials)) {
            return AuthAttemptStatus::FAILED;
        }

        if ($user->hasTwoFactorAuthEnabled()) {
            $this->startLoginWith2FA($user);

            return AuthAttemptStatus::TWO_FACTOR_AUTH;
        }

        $this->logIn($user);

        return AuthAttemptStatus::SUCCESS;
    }

    public function checkCredentials(UserInterface $user, array $credentials): bool
    {
        return password_verify($credentials['password'], $user->getPassword());
    }

    public function logOut(): void
    {
        $this->session->forget('user');
        $this->session->regenerate();

        $this->user = null;
    }

    public function register(RegisterUserData $data): UserInterface
    {
        $user = $this->userProvider->createUser($data);

        $this->logIn($user);

        $this->signupEmail->send($user);

        return $user;
    }

    public function logIn(UserInterface $user): void
    {
        $this->session->regenerate();
        $this->session->put('user', $user->getId());

        $this->user = $user;
    }

    public function startLoginWith2FA(UserInterface $user): void
    {
        $this->session->regenerate();
        $this->session->put('2fa', $user->getId());

        $this->userLoginCodeService->deactivateAllActiveCodes($user);

        $this->twoFactorAuthEmail->send($this->userLoginCodeService->generate($user));
    }

    public function attemptTwoFactorLogin(array $data): bool
    {
        $userId = $this->session->get('2fa');

        if (! $userId) {
            return false;
        }

        $user = $this->userProvider->getById($userId);

        if (! $user || $user->getEmail() !== $data['email']) {
            return false;
        }

        if (! $this->userLoginCodeService->verify($user, $data['code'])) {
            return false;
        }

        $this->session->forget('2fa');

        $this->logIn($user);

        $this->userLoginCodeService->deactivateAllActiveCodes($user);

        return true;
    }
}
