<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiDocController extends AbstractController
{
    #[Route('/api/doc', name: 'api_documentation', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('api_doc/index.html.twig');
    }
}
