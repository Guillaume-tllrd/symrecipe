<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientsType;
use App\Repository\IngredientRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IngredientsController extends AbstractController
{
    /**
     * This function display all ingredients
     *
     * @param IngredientRepository $ingredientRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredients', name: 'app_ingredients', methods: ['GET'])]
    public function index(IngredientRepository $ingredientRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $ingredientRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('pages/ingredients/index.html.twig', compact('ingredients'));
    }

    #[Route('/ingredients/nouveau', name: 'new_ingredient', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        $ingredients = new Ingredient();
        $form = $this->createForm(IngredientsType::class, $ingredients);

        return $this->render('pages/ingredients/new.html.twig', ['form' => $form->createView()]);
    }
}
