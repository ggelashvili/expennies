<?php

declare(strict_types=1);


namespace App;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Csrf
{

    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function failureHanddler(): \Closure
    {
        return fn (ServerRequestInterface $request, RequestHandlerInterface $handler)
            => $this->responseFactory->createResponse()->withStatus(403);
    }
}