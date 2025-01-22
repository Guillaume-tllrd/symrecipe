<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// pour email:
// use Symfony\Component\Mailer\MailerInterface;
// use Symfony\Bridge\Twig\Mime\TemplatedEmail;
// use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $manager, MailService $mailService): Response
    {
        $contact = new Contact();
        if ($this->getUser()) {
            $contact->setFullName($this->getUser()->getFullName())
                ->setEmail($this->getUser()->getEmail());
        }
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            $manager->persist($contact);
            $manager->flush();

            // Avec emeilService:
            $mailService->sendEmail(
                $contact->getEmail(),
                $contact->getSubject(),
                'emails/contact.html.twig',
                ['contact' => $contact]

            );
            // email, copié depuis: https://symfony.com/doc/current/mailer.html
            // $email = (new TemplatedEmail())
            //     ->from($contact->getEmail())
            //     ->to('admin@symrecipe.com')
            //     //->cc('cc@example.com')
            //     //->bcc('bcc@example.com')
            //     //->replyTo('fabien@example.com')
            //     //->priority(Email::PRIORITY_HIGH)
            //     ->subject($contact->getSubject())
            //     // path of the Twig template to render
            //     ->htmlTemplate('emails/contact.html.twig')

            //     // on récupère la var contact dans le fichier twig via le context
            //     ->context([
            //         'contact' => $contact,

            //     ]);
            // $mailer->send($email);


            $this->addFlash('success', 'Votre demande a été envoyée avec succès!');
            return $this->redirectToRoute("app_contact");
        }
        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
