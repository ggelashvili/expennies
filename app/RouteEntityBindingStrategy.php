<?php

declare(strict_types = 1);

namespace App;

use App\Contracts\EntityManagerServiceInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Slim\Interfaces\InvocationStrategyInterface;

class RouteEntityBindingStrategy implements InvocationStrategyInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        $callableReflection = $this->createReflectionForCallable($callable);
        $resolvedArguments  = [];

        foreach ($callableReflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type) {
                continue;
            }

            $paramName = $parameter->getName();
            $typeName  = $type->getName();

            if ($type->isBuiltin()) {
                if ($typeName === 'array' && $paramName === 'args') {
                    $resolvedArguments[] = $routeArguments;
                }
            } else {
                if ($typeName === ServerRequestInterface::class) {
                    $resolvedArguments[] = $request;
                } elseif ($typeName === ResponseInterface::class) {
                    $resolvedArguments[] = $response;
                } else {
                    $entityId = $routeArguments[$paramName] ?? null;

                    if (! $entityId || $parameter->allowsNull()) {
                        throw new \InvalidArgumentException(
                            'Unable to resolve argument "' . $paramName . '" in the callable'
                        );
                    }

                    $entity = $this->entityManagerService->find($typeName, $entityId);

                    if (! $entity) {
                        return $this->responseFactory->createResponse(404, 'Resource Not Found');
                    }

                    $resolvedArguments[] = $entity;
                }
            }
        }

        return $callable(...$resolvedArguments);
    }

    public function createReflectionForCallable(callable $callable): ReflectionFunctionAbstract
    {
        return is_array($callable)
            ? new ReflectionMethod($callable[0], $callable[1])
            : new ReflectionFunction($callable);
    }
}
