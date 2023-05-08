<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\ReceiptController;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;
use App\Controllers\VerifyController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\VerifyEmailMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function(RouteCollectorProxy $group) {
        $group->get('/', [HomeController::class, 'index']);

        $group->group('/categories', function (RouteCollectorProxy $categories) {
            $categories->get('', [CategoryController::class, 'index']);
            $categories->get('/load', [CategoryController::class, 'load']);
            $categories->post('', [CategoryController::class, 'store']);
            $categories->delete('/{category}', [CategoryController::class, 'delete']);
            $categories->get('/{category}', [CategoryController::class, 'get']);
            $categories->post('/{category}', [CategoryController::class, 'update']);
        });

        $group->group('/transactions', function (RouteCollectorProxy $transactions) {
            $transactions->get('', [TransactionController::class, 'index']);
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
    })->add(VerifyEmailMiddleware::class)->add(AuthMiddleware::class);

    $app->group('', function(RouteCollectorProxy $group) {
        $group->post('/logout', [AuthController::class, 'logOut']);
        $group->get('/verify', [VerifyController::class, 'index']);
    })->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);
};
