<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use Slim\App;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index']);

    $app->get('/login', [AuthController::class, 'loginView']);
    $app->get('/register', [AuthController::class, 'registerView']);
    $app->post('/login', [AuthController::class, 'logIn']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->post('/logout', [AuthController::class, 'logOut']);
};
