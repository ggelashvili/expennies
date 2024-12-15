<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TransactionData;
use App\Entity\Receipt;
use App\Entity\Transaction;
use App\RequestValidators\TransactionRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use App\Services\TransactionService;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(private readonly Twig $twig, private readonly RequestValidatorFactoryInterface $requestValidatorFactory, private readonly TransactionService $transactionService, private readonly ResponseFormatter $responseFormatter, private readonly RequestService $requestService, private readonly CategoryService $categoryService, private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'transactions/index.twig', ['categories' => $this->categoryService->getCategoryNames()]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate($request->getParsedBody());

        $transaction = $this->transactionService->create(new TransactionData($data['description'], (float)$data['amount'], new DateTime($data['date']), $data['category']), $request->getAttribute('user'));

        $this->entityManagerService->sync($transaction);

        return $response;
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $transaction = $this->transactionService->getById((int)$args['id']);
        $this->entityManagerService->delete($transaction);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $transaction = $this->transactionService->getById((int)$args['id']);

        if (!$transaction) {
            return $response->withStatus(404);
        }

        $data = ['id' => $transaction->getId(), 'description' => $transaction->getDescription(), 'amount' => $transaction->getAmount(), 'date' => $transaction->getDate()->format('Y-m-d\TH:i'), 'category' => $transaction->getCategory()->getId(),];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate($args + $request->getParsedBody());

        $id = (int)$data['id'];

        if (!$id || !($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $this->entityManagerService->sync($this->transactionService->update($transaction, new TransactionData($data['description'], (float)$data['amount'], new DateTime($data['date']), $data['category'])));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParameters($request);
        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $transformer = function (Transaction $transaction) {
            return ['id' => $transaction->getId(), 'description' => $transaction->getDescription(), 'amount' => $transaction->getAmount(), 'date' => $transaction->getDate()->format('m/d/Y g:i A'), 'category' => $transaction->getCategory()?->getName(), 'wasReviewed' => $transaction->wasReviewed(), 'receipts' => $transaction->getReceipts()->map(fn(Receipt $receipt) => ['name' => $receipt->getFilename(), 'id' => $receipt->getId(),])->toArray(),];
        };

        $totalTransactions = count($transactions);

        return $this->responseFormatter->asDataTable($response, array_map($transformer, (array)$transactions->getIterator()), $params->draw, $totalTransactions);
    }

    public function toggleReviewed(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        if (!$id || !($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $this->transactionService->toggleReviewed($transaction);
        $this->entityManagerService->sync();

        return $response;
    }
}
