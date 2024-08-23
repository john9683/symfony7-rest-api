<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
  use TargetPathTrait;

  /**
   * @param bool|null $emailIsVerified
   * @return Response|null
   */
  public function getResultVerifyEmail(?bool $emailIsVerified): ?Response
  {
    if ($emailIsVerified === null) {
      $this->addFlash('error_email_verified', 'Такой пользователь не найден, попробуйте ещё раз');
      return $this->redirectToRoute('app_register');
    }

    if (!$emailIsVerified) {
      $this->addFlash('error_email_verified', 'Ссылка для подтверждения email повреждена или устарела, попробуйте ещё раз');
      return $this->redirectToRoute('app_register');
    }

    return null;
  }

  /**
   * @param Request $request
   * @param UserService $userService
   * @return Response
   */
  #[Route(path: "/verify-registration-mail", name: "app_verify_registration_mail")]
  public function loginAfterVerifyEmail(Request $request, UserService $userService): Response
  {
    $emailIsVerified = $userService->verifyEmail(
      $request->query->get('id'),
      $request->getUri(),
      function () use ($userService, $request) {
        $userService->setEmailIsVerified($request->query->get('id'));
      }
    );

    $this->getResultVerifyEmail($emailIsVerified);

    $this->addFlash('success_email_verified', 'Ваш email подтверждён, вы можете войти на сайт');

    return $this->redirectToRoute('app_homepage');
  }

  /**
   * @param Request $request
   * @param UserService $userService
   * @return Response
   */
  #[Route(path: "/verify-updating-mail", name: "app_verify_updating_mail")]
  public function loginAfterVerifyUpdatingEmail(Request $request, UserService $userService): Response
  {
    $emailIsVerified = $userService->verifyEmail(
      $request->query->get('id'),
      $request->getUri(),
      function () use ($userService, $request) {
        $userService->setUpdatedEmail($request->query->get('id'));
      }
    );

    $this->getResultVerifyEmail($emailIsVerified);

    return $this->redirectToRoute('app_logout');
  }
}
