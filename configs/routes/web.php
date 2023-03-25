<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\ReceiptController;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);

    $app->group('/categories', function (RouteCollectorProxy $categories) {
        $categories->get('', [CategoryController::class, 'index']);
        $categories->get('/load', [CategoryController::class, 'load']);
        $categories->post('', [CategoryController::class, 'store']);
        $categories->delete('/{id:[0-9]+}', [CategoryController::class, 'delete']);
        $categories->get('/{id:[0-9]+}', [CategoryController::class, 'get']);
        $categories->post('/{id:[0-9]+}', [CategoryController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/transactions', function (RouteCollectorProxy $transactions) {
        $transactions->get('', [TransactionController::class, 'index']);
        $transactions->get('/load', [TransactionController::class, 'load']);
        $transactions->post('', [TransactionController::class, 'store']);
        $transactions->post('/import', [TransactionImporterController::class, 'import']);
        $transactions->delete('/{id:[0-9]+}', [TransactionController::class, 'delete']);
        $transactions->get('/{id:[0-9]+}', [TransactionController::class, 'get']);
        $transactions->post('/{id:[0-9]+}', [TransactionController::class, 'update']);
        $transactions->post('/{id:[0-9]+}/receipts', [ReceiptController::class, 'store']);
        $transactions->get(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'download']
        );
        $transactions->delete(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'delete']
        );
        $transactions->post('/{id:[0-9]+}/review', [TransactionController::class, 'toggleReviewed']);
    })->add(AuthMiddleware::class);
};
