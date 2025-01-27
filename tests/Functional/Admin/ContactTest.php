<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class Contacttets extends WebTestCase
{

    public function testCrudIsHere(): void
    {

        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => '1']);

        // on utilise la constante METHOD_GET de la méthode Request dans /admin
        $client->request(Request::METHOD_GET, '/admin');

        $this->assertResponseIsSuccessful();

        // Une fois qu'il s'est rendu sur /admin on va chercher le link demande de contact
        $crawler = $client->clickLink('Demandes de contact');

        $this->assertResponseIsSuccessful();

        $client->click($crawler->filter('.action.new'->link()));

        $this->assertResponseIsSuccessful();

        $client->request(Request::METHOD_GET, '/admin');

        $client->click($crawler->filter('.action.edit'->link()));

        $this->assertResponseIsSuccessful();
    }
    }
}
