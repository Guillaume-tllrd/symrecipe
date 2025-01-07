<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'edit_user', methods: ['GET', 'POST'])]
    /**
     * This controller allow us to edit user's profile
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_recette');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Les informations de votre compte ont bien été modifiées!');

            return $this->redirectToRoute('app_recette');
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('utilisateur/edition-mot-de-passe/{id}', name: 'edit_user_password', methods: ['GET', 'POST'])]
    /**
     * This controller allow us to edit password
     *
     * @param User $user
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_recette');
        }
        // comme on a pas newPassword dans l'entité user, on ne le rajoute pas apres le formulaire
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData()['plainPassword']);
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword']
                    )
                );

                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Le mot de passe a été modifié.');
                return $this->redirectToRoute('app_recette');
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect');
            }
            // arreté à 5:04:38
        };
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
