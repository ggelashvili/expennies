<?php

declare(strict_types = 1);

namespace App\Middleware;

use App\Exception\SessionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessionsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session has already been started');
        }

        if (headers_sent($fileName, $line)) {
            throw new SessionException('Headers already sent');
        }

        session_set_cookie_params(['secure' => true, 'httponly' => true, 'samesite' => 'lax']);

        session_start();

        $response = $handler->handle($request);

        session_write_close();

        return $response;
    }
}
