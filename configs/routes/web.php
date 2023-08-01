<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\PasswordResetController;
use App\Controllers\ProfileController;
use App\Controllers\ReceiptController;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;
use App\Controllers\VerifyController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\ValidateSignatureMiddleware;
use App\Middleware\VerifyEmailMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/', [HomeController::class, 'index'])->setName('home');
        $group->get('/stats/ytd', [HomeController::class, 'getYearToDateStatistics']);

        $group->group('/categories', function (RouteCollectorProxy $categories) {
            $categories->get('', [CategoryController::class, 'index'])->setName('categories');
            $categories->get('/load', [CategoryController::class, 'load']);
            $categories->post('', [CategoryController::class, 'store']);
            $categories->delete('/{category}', [CategoryController::class, 'delete']);
            $categories->get('/{category}', [CategoryController::class, 'get']);
            $categories->post('/{category}', [CategoryController::class, 'update']);
        });

        $group->group('/transactions', function (RouteCollectorProxy $transactions) {
            $transactions->get('', [TransactionController::class, 'index'])->setName('transactions');
            $transactions->get('/load', [TransactionController::class, 'load']);
            $transactions->post('', [TransactionController::class, 'store']);
            $transactions->post('/import', [TransactionImporterController::class, 'import']);
            $transactions->delete('/{transaction}', [TransactionController::class, 'delete']);
            $transactions->get('/{transaction}', [TransactionController::class, 'get']);
            $transactions->post('/{transaction}', [TransactionController::class, 'update']);
            $transactions->post('/{transaction}/receipts', [ReceiptController::class, 'store']);
            $transactions->get(
                '/{transaction}/receipts/{receipt}',
                [ReceiptController::class, 'download']
            );
            $transactions->delete(
                '/{transaction}/receipts/{receipt}',
                [ReceiptController::class, 'delete']
            );
            $transactions->post('/{transaction}/review', [TransactionController::class, 'toggleReviewed']);
        });

        $group->group('/profile', function (RouteCollectorProxy $profile) {
            $profile->get('', [ProfileController::class, 'index']);
            $profile->post('', [ProfileController::class, 'update']);
            $profile->post('/update-password', [ProfileController::class, 'updatePassword']);
        });
    })->add(VerifyEmailMiddleware::class)->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $group) {
        $group->post('/logout', [AuthController::class, 'logOut']);
        $group->get('/verify', [VerifyController::class, 'index']);
        $group->get('/verify/{id}/{hash}', [VerifyController::class, 'verify'])
              ->setName('verify')
              ->add(ValidateSignatureMiddleware::class);
        $group->post('/verify', [VerifyController::class, 'resend'])
              ->setName('resendVerification')
              ->add(RateLimitMiddleware::class);
    })->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn'])
              ->setName('logIn')
              ->add(RateLimitMiddleware::class);
        $guest->post('/register', [AuthController::class, 'register'])
              ->setName('register')
              ->add(RateLimitMiddleware::class);
        $guest->post('/login/two-factor', [AuthController::class, 'twoFactorLogin'])
              ->setName('twoFactorLogin')
              ->add(RateLimitMiddleware::class);
        $guest->get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm']);
        $guest->get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])
              ->setName('password-reset')
              ->add(ValidateSignatureMiddleware::class);
        $guest->post('/forgot-password', [PasswordResetController::class, 'handleForgotPasswordRequest'])
              ->setName('handleForgotPassword')
              ->add(RateLimitMiddleware::class);
        $guest->post('/reset-password/{token}', [PasswordResetController::class, 'resetPassword'])
              ->setName('resetPassword')
              ->add(RateLimitMiddleware::class);
    })->add(GuestMiddleware::class);
};
