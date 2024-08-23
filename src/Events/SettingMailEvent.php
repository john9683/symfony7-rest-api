<?php

namespace App\Events;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SettingMailEvent extends Event
{
  /**
   * @var User
   */
  private User $user;

  /**
   * @var string
   */
  private string $type;

  public function __construct(User $user, string $type)
  {
    $this->user = $user;
    $this->type = $type;
  }

  /**
   * @return User
   */
  public function getUser(): User
  {
    return $this->user;
  }

  /**
   * @return string
   */
  public function getType(): string
  {
    return $this->type;
  }
}
