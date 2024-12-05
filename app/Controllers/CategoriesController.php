<?php

declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class CategoriesController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'categories/index.twig',
        );
    }

    public function store(Request $request, Response $response): Response
    {
        //1. Validate request data
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidation::class)->validate(
            $request->getParsedBody()
        );
        //2. Create a mew category record in the database
        $this->auth->register(
            new RegisterUserData($data['name'], $data['email'], $data['password'])
        );

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Request $request, Response $response): Response
    {
        // TODO

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }
}
