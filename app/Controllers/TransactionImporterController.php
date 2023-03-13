<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TransactionData;
use App\RequestValidators\TransactionImportRequestValidator;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

class TransactionImporterController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService
    ) {
    }

    public function import(Request $request, Response $response): Response
    {
        /** @var UploadedFileInterface $file */
        $file = $this->requestValidatorFactory->make(TransactionImportRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['importFile'];

        $user       = $request->getAttribute('user');
        $resource   = fopen($file->getStream()->getMetadata('uri'), 'r');
        $categories = $this->categoryService->getAllKeyedByName();

        fgetcsv($resource);

        while (($row = fgetcsv($resource)) !== false) {
            [$date, $description, $category, $amount] = $row;

            $date     = new \DateTime($date);
            $category = $categories[strtolower($category)] ?? null;
            $amount   = str_replace(['$', ','], '', $amount);

            $transactionData = new TransactionData($description, (float) $amount, $date, $category);

            $this->transactionService->create($transactionData, $user);
        }

        return $response;
    }
}
