<?php

declare(strict_types = 1);

namespace App\Mail;

use App\Config;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class SignupEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer
    ) {
    }

    public function send(string $to): void
    {
        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($to)
            ->subject('Welcome to Expennies')
            ->htmlTemplate('emails/signup.html.twig')
            ->context(
                [
                    'activationLink' => '#',
                    'expirationDate' => new \DateTime('+30 minutes'),
                ]
            );

        $this->renderer->render($message);

        $this->mailer->send($message);
    }
}
