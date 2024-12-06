<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\Services\CategoryService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class CategoriesController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
    ){
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'categories/index.twig',
            [
                'categories' => $this->categoryService->getAll()
            ]
        );
    }

    public function store(Request $request, Response $response): Response
    {
        //1. Validate request data
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );
        //2. Create a mew category record in the database
        $this->categoryService->create(
            $data['name'], $request->getAttribute('user')
        );

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Request $request, Response $response): Response
    {
        // TODO

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }
}
