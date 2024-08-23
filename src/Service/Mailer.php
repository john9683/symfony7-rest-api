<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
  /**
   * @var MailerInterface
   */
  private object $mailer;

  public function __construct(
    MailerInterface $mailer
  )
  {
    $this->mailer = $mailer;
  }

  /**
   * @param string $template
   * @param string $subject
   * @param string $email
   * @param string $firstName
   * @param array|null $context
   * @param \Closure|null $callback
   * @return void
   */
  private function sendEmail(
    string $template,
    string $subject,
    string $email,
    string $firstName,
    ?Array $context,
    \Closure $callback = null
  ): void {
    $email = (new TemplatedEmail())
      ->from(new Address('mailer.jes@gmail.com', 'Mailer John' ))
      ->to(new Address($email, $firstName))
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($context)
    ;

    if (is_callable($callback) ) {
      $callback($email);
    }

    $this->mailer->send($email);
  }

  /**
   * @param $email
   * @param $firstName
   * @param array|null $context
   * @return void
   */
  public function sendConfirmMail($email, $firstName, ?Array $context): void
  {
    $this->sendEmail(
      'email/setting_mail.html.twig',
      'Подтвердите ваш email',
      $email,
      $firstName,
      $context
    );
  }
}
