<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\String\Slugger\SluggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $contactForm = $this->createForm(ContactType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {

            if (empty($contactForm['honeypot']->getData())) { // si le pot de miel est vide
                $contact = $contactForm->getData(); // récupère les informations du formulaire
                $email = (new TemplatedEmail()) // prépare un mail à partir d'un template Twig
                    ->from(new Address($contact['email'], $contact['first_name'] . ' ' . $contact['last_name'])) // expéditeur
                    ->to(new Address('david.hurtrel@gmail.com')) // destinataire
                    ->replyTo(new Address($contact['email'], $contact['first_name'] . ' ' . $contact['last_name']))
                    ->subject($contact['subject'])
                    ->htmlTemplate('email/contact_email.html.twig') // chemin du template de mail
                    ->context([ // passe les informations au template
                        'firstName' => $contact['first_name'],
                        'lastName' => $contact['last_name'],
                        'subject' => $contact['subject'],
                        'message' => $contact['message'],
                        'emailAddress' => $contact['email'],
                    ]);
                if ($contact['attachment'] !== null) { // vérifie s'il y a un fichier dans le formulaire
                    $originalFilename = pathinfo($contact['attachment']->getClientOriginalName(), PATHINFO_FILENAME); // récupère le nom original du fichier
                    $safeFilename = $slugger->slug($originalFilename); // nécessaire pour inclure le fichier dans l'URL
                    $newFileName = $safeFilename . '.' . $contact['attachment']->guessExtension(); // renomme le fichier pour lui ajouter une extension de fichier
                    $email->attachFromPath($contact['attachment']->getPathName(), $newFileName); // attache le fichier en pièce-jointe
                }
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('contact');
            } else {
                dd('You\'re a f****** robot, man !');
            }
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $contactForm->createView()
        ]);
    }
}
