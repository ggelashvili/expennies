<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Psr\Http\Message\UploadedFileInterface;

class TransactionImportRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $data['importFile'] ?? null;

        // 1. Validate uploaded file
        if (! $uploadedFile) {
            throw new ValidationException(['importFile' => ['Please select a CSV file']]);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new ValidationException(['importFile' => ['Upload failed']]);
        }

        // 2. Validate file size
        $maxFileSize = 10 * 1024 * 1024;

        if ($uploadedFile->getSize() > $maxFileSize) {
            throw new ValidationException(['importFile' => ['Maximum allowed size is 10 MB']]);
        }

        // 3. Validate the file name
        $filename = $uploadedFile->getClientFilename();

        if (! preg_match('/^[a-zA-Z0-9\s._-]+$/', $filename)) {
            throw new ValidationException(['importFile' => ['CSV file name contains invalid characters.']]);
        }

        // 4. Validate the file type
        $allowedMimeTypes = ['text/csv'];

        if (! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes)) {
            throw new ValidationException(['importFile' => ['File has to be CSV document']]);
        }

        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($uploadedFile->getStream()->getMetadata('uri'));

        if (! in_array($mimeType, $allowedMimeTypes)) {
            throw new ValidationException(['importFile' => ['Invalid file type']]);
        }

        return $data;
    }
}