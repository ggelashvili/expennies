<?php

declare(strict_types = 1);

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
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly CategoryService $categoryService,
        private readonly EntityManagerServiceInterface $entityManagerService,
        private readonly CacheInterface $cache
    ) {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render(
            $response,
            'transactions/index.twig',
            ['categories' => $this->categoryService->getCategoryNames()]
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $this->cache->clear();
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $transaction = $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            ),
            $request->getAttribute('user')
        );

        $this->entityManagerService->sync($transaction);

        return $response;
    }

    public function delete(Response $response, Transaction $transaction): Response
    {
        $this->cache->clear();
        $this->entityManagerService->delete($transaction, true);

        return $response;
    }

    public function get(Response $response, Transaction $transaction): Response
    {
        $this->cache->clear();
        $data = [
            'id'          => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'amount'      => $transaction->getAmount(),
            'date'        => $transaction->getDate()->format('d.m.Y H:i:s'),
            'category'    => $transaction->getCategory()?->getId(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, Transaction $transaction): Response
    {
        $this->cache->clear();
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $transaction = $this->transactionService->update(
            $transaction,
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            )
        );

        $this->entityManagerService->sync($transaction);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params   = $this->requestService->getDataTableQueryParams($request);
        $cacheKey = $request->getAttribute('user')->getId() . '_' . 'dashboard_' . $params->start . '_' . $params->length;
        if ($this->cache->has($cacheKey)) {
            return $this->responseFormatter->asDataTable(
                $response, ...$this->cache->get($cacheKey));
        }

        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $transformer  = function (Transaction $transaction) {
            return [
                'id'          => $transaction->getId(),
                'description' => $transaction->getDescription(),
                'amount'      => $transaction->getAmount(),
                'date'        => $transaction->getDate()->format('d.m.Y H:i:s'),
                'category'    => $transaction->getCategory()?->getName(),
                'wasReviewed' => $transaction->wasReviewed(),
                'receipts'    => $transaction->getReceipts()->map(fn(Receipt $receipt) => [
                    'name' => $receipt->getFilename(),
                    'id'   => $receipt->getId(),
                ])->toArray(),
            ];
        };

        $totalTransactions = count($transactions);

        $dashboard = [array_map($transformer, (array) $transactions->getIterator()),
            $params->draw,
            $totalTransactions];

        $this->cache->set($cacheKey, $dashboard);

        return $this->responseFormatter->asDataTable(
            $response,
            ...$dashboard
        );
    }

    public function toggleReviewed(Response $response, Transaction $transaction): Response
    {
        $this->cache->clear();
        $this->transactionService->toggleReviewed($transaction);
        $this->entityManagerService->sync();

        return $response;
    }
}