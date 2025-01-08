<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(RecipeRepository $recipes): Response
    {

        return $this->render('main/index.html.twig', [
            'recipes' => $recipes->findPulicRecipe(3)
        ]);
    }
}
