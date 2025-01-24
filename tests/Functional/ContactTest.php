<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactTest extends WebTestCase
{
    public function testIfSubmitContactFormIsSuccesfull(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Formulaire de contact');

        // Récupérer le formulaire:
        $submitButton = $crawler->selectButton('Soumettre ma demande');
        $form = $submitButton->form();
        // on récupère les champs du form:
        $form["contact[fullName]"] = 'Jean Dupont';
        $form["contact[email]"] = 'jd@symrecipe.com';
        $form["contact[subject]"] = 'Test';
        $form["contact[message]"] = 'Test';
        // soumettre le formulaire
        $client->submit($form);
        // Vérifier le statut HTTP
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Vérifier l'envoie du mail
        $this->assertEmailCount(1);

        $client->followRedirect();
        // Vérifier la présence du message de succès
        $this->assertSelectorTextContains('div.alert.alert-succes.mt-4', 'Votre demande a été envoyé avec succès!');
    }
    // il faut faire attention que le mailer_dsn dans le .env.test.local soit bien rempli pour ne pas avoir d'erreur

}
