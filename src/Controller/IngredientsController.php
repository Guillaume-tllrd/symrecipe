<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientsType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    /**
     * This controller show a form which create an ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/ingredients/nouveau', name: 'new_ingredient', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ingredients = new Ingredient();
        $form = $this->createForm(IngredientsType::class, $ingredients);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $ingredient = $form->getData();

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash('success', 'Votre ingrédient a été créé avec succès!');
            return $this->redirectToRoute("app_ingredients");
        }
        return $this->render('pages/ingredients/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('ingredients/edition/{id}', name: 'edit_ingredient', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $em): Response
    {
        // on utilise le repository pour récup l'ingrédient
        // $ingredient = $ingredientRepository->findOneBy(['id' => $id]); on utilise l'entité pour que l'ingredient soit directement recoonu pas besoin d'utiliser le repository
        $form = $this->createForm(IngredientsType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $ingredient = $form->getData();

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash('success', 'Votre ingrédient a été modifié avec succès!');
            return $this->redirectToRoute("app_ingredients");
        }
        return $this->render('pages/ingredients/edit.html.twig', ['form' => $form->createView()]);
    }

    #[Route('ingredients/suppression/{id}', name: 'delete_ingredient', methods: ['GET'])]
    public function delete(EntityManagerInterface $em, Ingredient $ingredient): Response
    {
        if (!$ingredient) {
            $this->addFlash('alert', 'L\'ingrédient en question n\'a pas été trouvé.');
            return $this->redirectToRoute("app_ingredients");
        }
        $em->remove($ingredient);
        $em->flush();

        $this->addFlash('success', 'Votre ingrédient a été supprimé avec succès!');
        return $this->redirectToRoute("app_ingredients");
    }
}
