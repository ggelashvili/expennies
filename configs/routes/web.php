<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Slim\App;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    $app->get('/login', [AuthController::class, 'loginView'])->add(GuestMiddleware::class);
    $app->get('/register', [AuthController::class, 'registerView'])->add(GuestMiddleware::class);
    $app->post('/login', [AuthController::class, 'logIn'])->add(GuestMiddleware::class);
    $app->post('/register', [AuthController::class, 'register'])->add(GuestMiddleware::class);
    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);
};
