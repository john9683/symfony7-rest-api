<?php

namespace App\EventSubscriber;

use App\Events\SettingMailEvent;
use App\Service\Mailer;
use App\Service\VerifyEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class SettingMailSubscriber implements EventSubscriberInterface
{
  public function __construct(Mailer $mailer, VerifyEmail $verifyEmail)
  {
    $this->mailer = $mailer;
    $this->verifyEmail = $verifyEmail;
  }

  /**
   * @param SettingMailEvent $event
   * @return void
   */
  public function onSettingMail(SettingMailEvent $event): void
  {
    if ($event->getType() === 'registration') {
      $context = [
        'confirmNewEmailLink' => $this->verifyEmail->getVerifyEmailLink(
          'app_verify_registration_mail',
          $event->getUser()
        ),
        'type' => 'registration'
      ];
      $email = $event->getUser()->getEmail();

    } elseif ($event->getType() === 'update') {
      $context = [
        'confirmUpdatedEmailLink' =>  $this->verifyEmail->getVerifyEmailLink(
          'app_verify_updating_mail',
          $event->getUser()
        ),
        'type' => 'update'
      ];
      $email = $event->getUser()->getPlainEmail();
    }

    $this->mailer->sendConfirmMail(
      $email,
      $event->getUser()->getFirstName(),
      $context
    );
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents(): array
  {
    return [
      SettingMailEvent::class => 'onSettingMail'
    ];
  }
}
