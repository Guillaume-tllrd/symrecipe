<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    // on appelle mailServicelà où on souhaite l'utiliser en passant les parameètre de sendEmqil
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $from,
        string $subject,
        string $htmlTemplate,
        array $context,
        string $to = 'admin@symrecipe.com'
    ): void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            // path of the Twig template to render
            ->htmlTemplate($htmlTemplate)

            // on récupère la var contact dans le fichier twig via le context
            ->context($context);

        $this->mailer->send($email);
    }
}
