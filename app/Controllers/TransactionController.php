<?php

declare(strict_types=1);


namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TransactionData;
use App\RequestValidators\CreateTransactionRequestValidator;
use App\Services\CategoryService;
use App\Services\TransactionService;
use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly CategoryService $categoryService,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'transactions/index.twig',
            ['categories' => $this->categoryService->getCategoryNames()]
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateTransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            ),
            $request->getAttribute('user')
        );

        return $response;
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->transactionService->delete((int) $args['id']);
        return $response;
    }

}