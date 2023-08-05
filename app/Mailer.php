<?php

declare(strict_types = 1);

namespace App;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class Mailer implements MailerInterface
{
    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        $adapter    = new LocalFilesystemAdapter(STORAGE_PATH . '/mail');
        $filesystem = new Filesystem($adapter);

        $filesystem->write(time() . '_' . uniqid(more_entropy: true) . '.eml', $message->toString());
    }
}
