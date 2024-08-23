<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: 'integer')]
  private int $id;

  #[ORM\Column(type: 'string', length: 180, unique: true)]
  private string $email;

  #[ORM\Column(type: 'json')]
  private array $roles = [];

  #[ORM\Column(type: 'string')]
  private string $password;

  #[ORM\Column(type: 'string', length: 90)]
  private string $firstName;

  #[ORM\Column(type: 'boolean')]
  private bool $isVerified;

  #[ORM\Column(type: 'string', length: 255)]
  private string $ApiToken;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  private string $plainEmail;

  /**
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
   * @param string $email
   * @return User
   */
  public function setEmail(string $email): User
  {
    $this->email = $email;

    return $this;
  }

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   * @return string
   */
  public function getUserIdentifier(): string
  {
    return $this->email;
  }


  /**
   * @see UserInterface
   * @return array
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  /**
   * @param array $roles
   * @return User
   */
  public function setRoles(array $roles): User
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   * @return string
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * @param string $password
   * @return $this
   */
  public function setPassword(string $password): User
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @see UserInterface
   * @return string|null
   */
  public function getSalt(): ?string

    /**
     * @param string $password
     * @return User
     */
  {
    return null;
  }

  /**
   * @see UserInterface
   * @return void
   */
  public function eraseCredentials(): void
  {
    $this->plainPassword = null;
  }

  /**
   * @return string
   */
  public function getFirstName(): string
  {
    return $this->firstName;
  }

  /**
   * @param string $firstName
   * @return User
   */
  public function setFirstName(string $firstName): User
  {
    $this->firstName = $firstName;

    return $this;
  }

  /**
   * @return bool
   */
  public function isIsVerified(): bool
  {
    return $this->isVerified;
  }

  /**
   * @param bool $isVerified
   * @return User
   */
  public function setIsVerified(bool $isVerified): self
  {
    $this->isVerified = $isVerified;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getApiToken(): ?string
  {
    return $this->ApiToken;
  }

  /**
   * @param string $ApiToken
   * @return $this
   */
  public function setApiToken(string $ApiToken): self
  {
    $this->ApiToken = $ApiToken;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getPlainEmail(): ?string
  {
    return $this->plainEmail;
  }

  /**
   * @param string|null $plainEmail
   * @return $this
   */
  public function setPlainEmail(?string $plainEmail): self
  {
    $this->plainEmail = $plainEmail;

    return $this;
  }
}
