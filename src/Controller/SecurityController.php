<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    /**
     * This controller allow us to login
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
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
    /**
     * This controller allow us to logout
     *
     * @return void
     */
    public function logout()
    {
        // rien à faire ici, d'après la doc, tt va être fait lui même
    }

    #[Route('/inscription', name: 'app_registration', methods: ['GET', 'POST'])]
    /**
     * This controller allow us to register
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function registration(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User;
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a bien été créé');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('pages/security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
