<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Receipt;
use App\Entity\Transaction;
use App\RequestValidators\UploadReceiptRequestValidator;
use App\Services\ReceiptService;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ReceiptService $receiptService,
        private readonly EntityManagerServiceInterface $entityManagerService
    ) {
    }

    public function store(Request $request, Response $response, Transaction $transaction): Response
    {
        /** @var UploadedFileInterface $file */
        $file     = $this->requestValidatorFactory->make(UploadReceiptRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['receipt'];
        $filename = $file->getClientFilename();

        $randomFilename = bin2hex(random_bytes(25));

        $this->filesystem->write('receipts/' . $randomFilename, $file->getStream()->getContents());

        $receipt = $this->receiptService->create($transaction, $filename, $randomFilename, $file->getClientMediaType());

        $this->entityManagerService->sync($receipt);

        return $response;
    }

    public function download(Response $response, Transaction $transaction, Receipt $receipt): Response
    {
        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            return $response->withStatus(401);
        }

        $file = $this->filesystem->readStream('receipts/' . $receipt->getStorageFilename());

        $response = $response->withHeader('Content-Disposition', 'inline; filename="' . $receipt->getFilename() . '"')
                             ->withHeader('Content-Type', $receipt->getMediaType());

        return $response->withBody(new Stream($file));
    }

    public function delete(Response $response, Transaction $transaction, Receipt $receipt): Response
    {
        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            return $response->withStatus(401);
        }

        $this->filesystem->delete('receipts/' . $receipt->getStorageFilename());

        $this->entityManagerService->delete($receipt, true);

        return $response;
    }
}
