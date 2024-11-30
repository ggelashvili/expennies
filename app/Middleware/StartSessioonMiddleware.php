<?php

namespace App\Middleware;

use App\Exception\SessionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessioonMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if(session_status() === PHP_SESSION_ACTIVE){
            throw new SessionException('Session is already started');
        }

        if (headers_sent($file, $line)) {
            throw new SessionException('Headers already sent');
        }
        session_start();

        $respnse = $handler->handle($request);

        session_write_close();

        return $respnse;
    }
}