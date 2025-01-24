<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        // avec crawler on va avoir accès à plusieurs méthodes:
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        // pour test que l'on a un button  
        $button = $crawler->filter('.btn.btn-primary.btn-lgz');
        $this->assertEquals(1, count($button));

        // pour vérifier que l'on a " recettes:
        $recipes = $crawler->filter(".recipes .card");
        $this->assertEquals(3, count($recipes));

        // si on cherche à montrer que le h1 est égal à Bienvenue sur SymRecipe:
        $this->assertSelectorTextContains('h1', 'Bienvenue sur SymRecipe');
    }
}
