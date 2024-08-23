<?php

namespace App\Security;

use AllowDynamicProperties;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;

#[AllowDynamicProperties]
class ApiTokenAuthenticator extends AbstractAuthenticator
{
  public function __construct(UserService $userService, EntityManagerInterface $em)
  {
    $this->userService = $userService;
    $this->em = $em;
  }

  /**
   * @param Request $request
   * @return bool|null
   */
  public function supports(Request $request): null|bool
  {
    return $request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
  }

  /**
   * @param Request $request
   * @return Passport
   */
  public function authenticate(Request $request): Passport
  {
    $token = substr($request->headers->get('Authorization'), 7);

    $user = $this->em->getRepository(User::class)->findOneBy(['ApiToken' => $token]);

    if (!$user) {
      throw new CustomUserMessageAuthenticationException('The user does not exist. Access denied');
    }

    if (!$user->isIsVerified()) {
      throw new CustomUserMessageAuthenticationException('The user is not verified. Access denied');
    }

    $email = $user->getUserIdentifier();
    $password = $user->getPassword();

    return new Passport(
      new UserBadge($email),
      new CustomCredentials(function ($password, User $user) {
        return $password === $user->getPassword();
      }, $password)
    );
  }

  /**
   * @param Request $request
   * @param TokenInterface $token
   * @param string $firewallName
   * @return Response|null
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
  {
    return null;
  }

  /**
   * @param Request $request
   * @param AuthenticationException $exception
   * @return Response|null
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
  {
    return new JsonResponse([
      'message' => $exception->getMessage(),
    ], 401);
  }

}
