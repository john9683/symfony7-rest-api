<?php

namespace App\Controller\Api;

use App\Service\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
  /**
   * @param Request $request
   * @param UserService $userService
   * @return JsonResponse
   */
  #[Route(path: "/api/user", name: "app_api_register_user", methods: ["POST"])]
  public function registerUser(Request $request, UserService $userService): JsonResponse
  {
    if ($userService->isEmailExist($request->toArray()['email'])) {
      return  $this->json([
        'message' => 'Пользователь с таким email уже существует'
      ], 403);
    }

    $response = $userService->registerByApi($request);

    return $this->json($userService->getUserByApi($response['id']), 201);
  }

  /**
   * @param string $id
   * @param UserService $userService
   * @return JsonResponse
   */
  #[Route(path: "/api/user/{id}", name: "app_api_get_user", methods: ["GET"])]
  public function getUserById(string $id, UserService $userService): JsonResponse
  {
    if (!$userService->getUserById($id)) {
      return  $this->json([
        'message' => 'Пользователь с таким id не существует'
      ], 404);
    }

    return $this->json($userService->getUserByApi($id));
  }

  /**
   * @param UserService $userService
   * @param PaginatorInterface $paginator
   * @param Request $request
   * @return JsonResponse
   */
  #[Route(path: "/api/user", name: "app_api_get_user_page", methods: ["GET"])]
  public function getAllUser(UserService $userService, PaginatorInterface $paginator, Request $request): JsonResponse
  {
    $pagination = $paginator->paginate(
      $userService->getAllUserByApi($request),
      $request->query->getInt('page', 1),
      !$request->query->get('limitPerPage') ? 2 : $request->query->get('limitPerPage')
    );

    $page = [];

    foreach ($pagination as $item) {
      $page[] = $item;
    }

    return $this->json($page);
  }

  /**
   * @param string $id
   * @param Request $request
   * @param UserService $userService
   * @return JsonResponse
   */
  #[Route(path: "/api/user/{id}", name: "app_api_update_user", methods: ["PATCH"])]
  public function updateUserById(string $id, Request $request, UserService $userService): JsonResponse
  {
    if (!$userService->getUserById($id)) {
      return  $this->json([
        'message' => 'Пользователь с таким id не существует'
      ], 404);
    }

    $userService->updateUserByApi($id, $request);

    return $this->json($userService->getUserByApi($id));
  }

  /**
   * @param string $id
   * @param UserService $userService
   * @return JsonResponse
   */
  #[Route(path: "/api/user/{id}", name: "app_api_delete_user", methods: ["DELETE"])]
  public function deleteUserById(string $id, UserService $userService): JsonResponse
  {
    if (!$userService->getUserById($id)) {
      return  $this->json([
        'message' => 'Пользователь с таким id не существует'
      ], 404);
    }

    $userService->deleteUserByApi($id);

    return $this->json([], 204);
  }
}
