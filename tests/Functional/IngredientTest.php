<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IngredientTest extends WebTestCase
{
    public function testIfCreateIngredientIsSuccessfull(): void
    {
        $client = static::createClient();
        // recup urlgenrator
        $urlGenerator = $client->getContainer()->get('router');
        // recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        // on récup le 1er utilisateur:
        $user = $entityManager->find(User::class, 1);
        $client->loginUser($user);

        // se rendre sur la page de création d'un ingrédient
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('new_ingredient'));
        // gérer le formulaaire
        $form = $crawler->filter('form[name=ingredient]')->form([
            'ingredient[name]' => "un ingredient",
            'ingredient[price]' => floatval(33)
        ]);
        $client->submit($form);

        // gérer la redirection
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        // gérer l'alert box et la route
        $this->assertSelectorTextContains('div.alert-success', "votre ingrédient a été créé avec succès!");
        $this->assertrouteSame('ingredient.index');
    }

    public function testIfListIngredientIsSuccesfull(): void
    {
        //on test juste la route pour voir si ca fonctionne
        $client = static::createClient();
        // recup urlgenrator
        $urlGenerator = $client->getContainer()->get('router');
        // recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        // on récup le 1er utilisateur:
        $user = $entityManager->find(User::class, 1);
        $client->loginUser($user);

        // se rendre sur la page de création d'un ingrédient
        $client->request(Request::METHOD_GET, $urlGenerator->generate('app_ingredients'));

        $this->assertResponseIsSuccessful();

        $this->assertRouteSame('app_ingredients');
    }

    public function testIfUpdateAnIngredientIsSuccesfull(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        // recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        // on récup le 1er utilisateur:
        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);
        $client->loginUser($user);

        // se rendre sur la page d'édition d'un ingrédient , faire passer l'id
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('edit_ingredient', ['id' => $ingredient->getId()]));

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=ingredient]')->form([
            'ingredient[name]' => "un ingredient 2",
            'ingredient[price]' => floatval(34)
        ]);

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert-success', "votre ingrédient a été modifié avec succès!");

        $this->assertRouteSame('app_ingredients');
    }

    public function testIfDeleteAnIngredientIsSuccessful(): void
    {

        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        // recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        // on récup le 1er utilisateur:
        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);
        $client->loginUser($user);

        // se rendre sur la page d'édition d'un ingrédient , faire passer l'id
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('delete_ingredient', ['id' => $ingredient->getId()]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert-success', "votre ingrédient a été supprimé avec succès!");

        $this->assertRouteSame('app_ingredients');
    }
}
