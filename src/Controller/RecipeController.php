<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;

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

    /**
     * This controller allow us to create a new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PictureService $pictureService
     * @return Response
     */
    #[Route('/recette/creation', name: 'new_recipe', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, PictureService $pictureService): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
            $images = $form->get('images')->getData();
            // dd($form->getData());
            foreach ($images as $image) {
                // on définit le dossier de destination:
                $folder = 'recipes';

                // on appelle le service d'ajout PictureService
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $recipe->addImage($img);
            }
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Votre recette a été créée avec succès!');
            return $this->redirectToRoute("app_recette");
        }
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView(),

        ]);
    }
    // /!\ Faire attention à mettre la route recette/publique avant recette/{id} sinon il y a un conflit
    #[Route('recette/publique', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(
        PaginatorInterface $paginator,
        RecipeRepository $recipeRepository,
        Request $request
    ): Response {

        $cache = new FilesystemAdapter();
        // on créé une var data pour récupérer le contenue qui est mis en cache via une clé, le FilesystemAdapter à une méthode qui s'appelle get et qui demande une clé qu'on appelle recipes, s'il ne l'a pas il demande un callable qu'on symbolise avec une function qui prend ItemInterface et on lui demande de faire un return des recettes publique avec le repository. On est obligé d'utiliser use car sinon le repository n'est pas reconnu
        $data = $cache->get('recipes', function (ItemInterface $item) use ($recipeRepository) {
            // on met en place le systeme d'expiration avec itemInterface:
            $item->expiresAfter(15);
            // ne pas oublier le return
            return $recipeRepository->findPulicRecipe(null);
        });

        $recipes = $paginator->paginate(
            // $recipeRepository->findPulicRecipe(null),:on remplace le repository avec data du cache
            $data,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('recette/{id}', name: 'recipe.show', methods: ['GET', 'POST'])]
    /**
     * This controller allow us to see a recipe if this one is public
     *
     * @param Recipe $recipe
     * @return Response
     */
    public function show(Recipe $recipe, Request $request, MarkRepository $markRepository, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        // on met une permission enfonction du bool
        if ($recipe->IsPublic() !== true && $this->getUser() !== $recipe->getUser()) {
            throw $this->createAccessDeniedException("Cette recette n'est pas en public.");
        }


        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $mark->setUser($this->getUser())->setRecipe($recipe);

            // pour être sûr qu'un user ne peut pas faire 2 fois la même recette:
            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            // si il n'ya pas d'existingMark alors tu persist sinon tu prend tu refixe
            if (!$existingMark) {
                $em->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
                // dd($existingMark);
            }
            $em->flush();
            $this->addFlash('success', 'Votre note a bien été prise en compte.');
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
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
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em, PictureService $pictureService): Response
    {

        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette recette.");
        }
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $recipe = $form->getData();

            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                // on définit le dossier de destination:
                $folder = 'recipes';

                // on appelle le service d'ajout PictureService
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $recipe->addImage($img);
            }
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Votre recette a été modifiée avec succès!');
            return $this->redirectToRoute("app_recette");
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView(),
            'recipe' => $recipe
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

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        // On récupère le contenu de la requête 
        $data = json_decode($request->getContent(), true);
        // on récupère e csrfToken qui s'appelle delete dans data-token depuis le a dans le form:
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // le token csrf est valide
            // on récupère le nom de l'image:
            $nom = $image->getName();

            if ($pictureService->delete($nom, 'recipes', 300, 300)) {
                // on envoie à la bdd
                $em->remove($image);
                $em->flush();

                return new JsonResponse(["success" => true], 200);
            }
            // si on rntre dans pas dans le if , la suppreission a échoué: 
            return new JsonResponse(["error" => 'Erreur de suppression'], 400);
        }
        return new JsonResponse(["error" => 'Token invalide'], 400);
    }
}
