<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        // on a changé le nom de la route et de la page twig manuellement
        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout()
    {
        // rien à faire ici, d'après la doc, tt va être fait lui même
    }
}
