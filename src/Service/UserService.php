<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\User;
use App\Events\SettingMailEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[AllowDynamicProperties]
class UserService
{
  protected $flash = [];

  public function __construct(
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher,
    EventDispatcherInterface $dispatcher,
    VerifyEmailHelperInterface $verifyEmailHelper,
    UserRepository $repository
  ) {
    $this->em = $em;
    $this->passwordHasher = $passwordHasher;
    $this->dispatcher = $dispatcher;
    $this->verifyEmailHelper = $verifyEmailHelper;
    $this->repository = $repository;
  }

  /**
   * @param Request $request
   * @return array
   */
  public function registerByApi(Request $request): array
  {
    $arguments = $request->toArray();

    $user = $this->createUser(
      $arguments['email'],
      $arguments['name'],
      $arguments['password']
    );

    return [
      'id' => $user->getId(),
      'name' => $user->getFirstName(),
      'email' => $user->getEmail()
    ];
  }

  /**
   * @param string $id
   * @return array
   */
  public function getUserByApi(string $id): array
  {
    /** @var User $user */
    $user = $this->getUserById($id);

    return [
      'id' => $user->getId(),
      'name' => $user->getFirstName(),
      'email' => $user->getEmail()
    ];
  }

  /**
   * @param Request $request
   * @return array
   */
  public function getAllUserByApi(Request $request): array
  {
    $userArray = [];

    $userArrayObject = $request->get('verified')
                      ? $this->repository->getAllVerifiedUser(filter_var($request->get('verified'), FILTER_VALIDATE_BOOLEAN))
                      : $this->em->getRepository(User::class)->findBy([], ['id' => 'asc']);

    foreach ($userArrayObject as $user) {
      $userArray[] = [ 'id' => $user->getId(), 'email' => $user->getEmail(), 'name' => $user->getFirstName() ];
    }

    return $userArray;
  }

  /**
   * @param string $id
   * @param Request $request
   * @return array
   */
  public function updateUserByApi(string $id, Request $request): array
  {
    $emailIsChanged = false;
    $arguments = $request->toArray();

    /** @var User $user */
    $user = $this->getUserById($id);

    if (isset($arguments['email'])) {
      if ($arguments['email'] !== '' && $arguments['email'] !== $user->getEmail()) {
        $user->setPlainEmail($arguments['email']);
        $this->em->flush();
        $emailIsChanged = true;

        $this->dispatcher->dispatch(new SettingMailEvent($user, 'update'));
      }
    }

    if (isset($arguments['name'])) {
      if  ($arguments['name'] !== '' && $arguments['name'] !== $user->getFirstName()) {
        $user->setFirstName($arguments['name']);
        $this->em->flush();
      }
    }

    if (isset($arguments['password'])) {
      $this->upgradePassword($user, $arguments['password']);
      $this->em->flush();
    }

    $user = $this->getUserById($id);

    return [
      'id' => $user->getId(),
      'name' => $user->getFirstName(),
      'email' => $emailIsChanged ? $user->getPlainEmail() : $user->getEmail()
    ];
  }

  /**
   * @param string $id
   * @return void
   */
  public function deleteUserByApi(string $id): void
  {
    /** @var User $user */
    $user = $this->getUserById($id);

    $this->em->remove($user);
    $this->em->flush();
  }

  /**
   * @param int|null $id
   * @return User|null
   */
  public function getUserById(?int $id): ?User
  {
    return $this->em->getRepository(User::class)->findOneBy(['id' => $id]);
  }

  /**
   * @param string $email
   * @return User
   */
  public function getUserByEmail(string $email): User
  {
    return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
  }

  /**
   * @param string $email
   * @return bool
   */
  public function isEmailExist(string $email): bool
  {
    return (bool)$this->em->getRepository(User::class)->findOneBy(['email' => $email]);
  }

  /**
   * @param string $email
   * @param string $firstName
   * @param string $plainPassword
   * @param bool $isVerified
   * @param string $role
   * @return User
   */
  public function createUser(
    string $email,
    string $firstName,
    string $plainPassword,
    bool $isVerified = false,
    string $role = 'ROLE_USER'
  ): User {
    $user = new User();

    $user
      ->setEmail($email)
      ->setFirstName($firstName)
      ->setRoles([$role])
      ->setIsVerified($isVerified)
      ->setPassword($this->passwordHasher->hashPassword(
        $user,
        $plainPassword
      ))
      ->setApiToken(sha1(uniqid('token')))
    ;

    $this->em->persist($user);
    $this->em->flush();

    $this->dispatcher->dispatch(new SettingMailEvent($user, 'registration'));

    return $user;
  }

  /**
   * @param PasswordAuthenticatedUserInterface $user
   * @param string $newHashedPassword
   * @return void
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf(
        'Instances of "%s" are not supported.', \get_class($user)));
    }

    $user->setPassword($this->passwordHasher->hashPassword(
      $user,
      $newHashedPassword
    ));

    $this->em->persist($user);
    $this->em->flush();
  }

  /**
   * @param string $id
   * @return void
   */
  public function setEmailIsVerified(string $id): void
  {
    $user = $this->getUserById($id);
    $user->setIsVerified(true);
    $this->em->flush();
  }

  /**
   * @param string $id
   * @return void
   */
  public function setUpdatedEmail(string $id): void
  {
    $user = $this->getUserById($id);
    $user->setEmail($user->getPlainEmail());
    $user->setPlainEmail('confirmed');
    $this->em->flush();
  }

  /**
   * @param string $id
   * @param string $uri
   * @param callable $function
   * @return bool|null
   */
  public function verifyEmail(string $id, string $uri, callable $function): ?bool
  {
    $user = $this->getUserById($id);
    if (!$user) {
      return null;
    }

    try {
      $this->verifyEmailHelper->validateEmailConfirmation(
        $uri,
        $user->getId(),
        $user->getEmail(),
      );
    } catch (VerifyEmailExceptionInterface $e) {
      return false;
    }

    $function($id);

    return true;
  }
}
