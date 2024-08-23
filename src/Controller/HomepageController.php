<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
  #[Route(path: "/", name: "app_homepage")]
  public function homepage(): Response
  {
    return $this->render('free_access/homepage/index.html.twig', [
      'showPartialNavbarMiddle' => true,
    ]);
  }
}
