<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Psr\Http\Message\UploadedFileInterface;

class UploadReceiptRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $data['receipt'] ?? null;

        // 1. Validate uploaded file
        if (! $uploadedFile) {
            throw new ValidationException(['receipt' => ['Please select a Receipt file']]);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new ValidationException(['receipt' => ['Upload failed']]);
        }

        // 2. Validate file size
        $maxFileSize = 5 * 1024 * 1024;

        if ($uploadedFile->getSize() > $maxFileSize) {
            throw new ValidationException(['receipt' => ['Maximum allowed size is 5 MB']]);
        }

        // 3. Validate the file name
        $filename = $uploadedFile->getClientFilename();

        if (! preg_match('/^[a-zA-Z0-9\s._-]+$/', $filename)) {
            throw new ValidationException(['receipt' => ['Receipt name contains invalid characters.']]);
        }

        // 4. Validate the file type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $tmpFilePath      = $uploadedFile->getStream()->getMetadata('uri');

        if (! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes)) {
            throw new ValidationException(['receipt' => ['Receipt has to be either an image or a pdf document']]);
        }

        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($tmpFilePath);

        if (! in_array($mimeType, $allowedMimeTypes)) {
            throw new ValidationException(['receipt' => ['Invalid file type']]);
        }

        return $data;
    }
}