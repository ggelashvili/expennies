<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    $app->get('/login', [AuthController::class, 'loginView']);
    $app->get('/register', [AuthController::class, 'registerView']);
    $app->post('/login', [AuthController::class, 'logIn']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);
};
