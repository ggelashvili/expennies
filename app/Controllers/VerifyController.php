<?php

declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'auth/verify.twig');
    }
}
