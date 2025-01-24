<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends WebTestCase
{
    public function testLoginIsSuccesfull(): void
    {
        $client = static::createClient();
        // Get route by urlgenerator
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('app_login'));

        // form
        // il faut rajouter le name sur la balise form au fichier twig si ce n'est pas fait
        $form = $crawler->filter("form[name=login]")->form(["_username" => "admin@symrecipe.fr", "_password" => "azerty"]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);


        // Redirect + home  
        $client->followRedirect();

        $this->assertRouteSame('app_main');
        // ensuie créer un MakeFile pour automatisé les procédures
    }

    public function testIfLoginFailedwhenPasswordIsWrong()
    {

        $client = static::createClient();
        // Get route by urlgenerator
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('app_login'));

        // form
        // il faut rajouter le name sur la balise form au fichier twig si ce n'est pas fait
        $form = $crawler->filter("form[name=login]")->form(["_username" => "admin@symrecipe.fr", "_password" => "OnChangeLeMdp"]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // il nous demande tjrs d'être redirigé sauf que du fait qu'on mette un mauvais mdp on se redirige vers le login
        $client->followRedirect();

        $this->assertRouteSame('app_login');

        // plus on peut tester le message d'erreur qu'on obtient:
        $this->assertSelectorTextContains("div.alert-danger", "Invalid credentials.");
    }
}
