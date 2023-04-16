<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Category;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class CategoryController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly EntityManagerServiceInterface $entityManagerService
    ) {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $this->categoryService->create($data['name'], $request->getAttribute('user'));

        $this->entityManagerService->sync($category);

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Response $response, Category $category): Response
    {
        $this->entityManagerService->delete($category, true);

        return $response;
    }

    public function get(Response $response, Category $category): Response
    {
        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, Category $category): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->entityManagerService->sync($this->categoryService->update($category, $data['name']));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $categories  = $this->categoryService->getPaginatedCategories($params);
        $transformer = function (Category $category) {
            return [
                'id'        => $category->getId(),
                'name'      => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
                'updatedAt' => $category->getUpdatedAt()->format('m/d/Y g:i A'),
            ];
        };

        $totalCategories = count($categories);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $categories->getIterator()),
            $params->draw,
            $totalCategories
        );
    }
}
