<?php

declare(strict_types = 1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class OldFormDataMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! empty($_SESSION['old'])) {
            $old = $_SESSION['old'];

            $this->twig->getEnvironment()->addGlobal('old', $old);

            unset($_SESSION['old']);
        }

        return $handler->handle($request);
    }
}
