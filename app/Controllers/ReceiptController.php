<?php

declare(strict_types = 1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

class ReceiptController
{
    public function store(Request $request, Response $response, array $args): Response
    {
        /** @var UploadedFileInterface $file */
        $file = $request->getUploadedFiles()['receipt'];

        return $response;
    }
}
