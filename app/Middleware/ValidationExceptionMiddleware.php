<?php

declare(strict_types = 1);

namespace App\Middleware;

use App\Exception\ValidationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse();
            $referer  = $request->getServerParams()['HTTP_REFERER'];
            $oldData  = $request->getParsedBody();

            $sensitiveFields = ['password', 'confirmPassword'];

            $_SESSION['errors'] = $e->errors;
            $_SESSION['old']    = array_diff_key($oldData, array_flip($sensitiveFields));

            return $response->withHeader('Location', $referer)->withStatus(302);
        }
    }
}
