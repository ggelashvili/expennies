<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config;
use App\Contracts\UserInterface;
use App\SignedUrl;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class SignupEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer,
        private readonly SignedUrl $signedUrl,
    ) {
    }

    public function send(UserInterface $user): void
    {
        $expirationDate = new DateTime('+30 minutes');
        $email = $user->getEmail();

        $activationLink = $this->signedUrl->fromRoute('verify', ['id' => $user->getId(), 'hash' => sha1($email)], $expirationDate);

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($email)
            ->subject('Welcome to Expennies')
            ->htmlTemplate('emails/signup.html.twig')
            ->context([
                'activationLink' => $activationLink,
                'expirationDate' => $expirationDate,
            ]);

        $this->renderer->render($message);

        $this->mailer->send($message);
    }
}