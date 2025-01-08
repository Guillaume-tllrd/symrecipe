<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param PaginatorInterface $paginator
     * @param RecipeRepository $recipeRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'app_recette', methods: ['GET'])]

    public function index(PaginatorInterface $paginator, RecipeRepository $recipeRepository, Request $request): Response
    {
        // praeil que ingredient on change la méthode finAll pour findBy
        $recipes = $paginator->paginate(
            $recipeRepository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
    // /!\ Faire attention à mettre la route recette/publique avant recette/{id} sinon il y a un conflit
    #[Route('recette/publique', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(
        PaginatorInterface $paginator,
        RecipeRepository $recipeRepository,
        Request $request
    ): Response {
        $recipes = $paginator->paginate(
            $recipeRepository->findPulicRecipe(12),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('recette/{id}', name: 'recipe.show', methods: ['GET'])]
    /**
     * This controller allow us to see a recipe if this one is public
     *
     * @param Recipe $recipe
     * @return Response
     */
    public function show(Recipe $recipe): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        // on met une permission enfonction du bool
        if ($recipe->IsPublic() !== true) {
            throw $this->createAccessDeniedException("Cette recette n'est pas en public.");
        }
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }



    /**
     * This controller allow us to create a new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/recette/creation', name: 'new_recipe', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Votre recette a été créée avec succès!');
            return $this->redirectToRoute("app_recette");
        }
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller allow us to edit a recipe
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/recette/edition/{id}', name: 'edit_recipe', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response
    {

        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette recette.");
        }
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $recipe = $form->getData();

            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Votre recette a été modifiée avec succès!');
            return $this->redirectToRoute("app_recette");
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('recette/suppression/{id}', name: 'delete_recipe', methods: ['GET'])]
    /**
     * This conttoller alow us to delete recipe
     *
     * @param EntityManagerInterface $em
     * @param Recipe $recipe
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Recipe $recipe): Response
    {

        $em->remove($recipe);
        $em->flush();

        $this->addFlash('success', 'Votre recette a été supprimé avec succès!');
        return $this->redirectToRoute("app_ingredients");
    }
}
