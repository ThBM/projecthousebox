<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 31/01/2018
 * Time: 13:35
 */

namespace App\Controller;


use App\Entity\ChangeEmail;
use App\Entity\ChangePassword;
use App\Entity\Client;
use App\Entity\ClientProjet;
use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Projet;
use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Form\ClientProfilType;
use App\Form\ProjetEditType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ClientController extends Controller
{
    /**
     * @Route("/client", name="client_home")
     */
    public function home() {
        return $this->render("Client/projets.html.twig");
    }

    /**
     * @Route("/client/profil", name="client_profil")
     */
    public function showProfil(Request $request, ObjectManager $manager) {
        //TODO
        /** @var Client $client */
        $client = $this->getUser();

        $form = $this->createForm(ClientProfilType::class, $client);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", "Le profil a été enregistré.");
        }

        return $this->render("Client/client_profil.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/client/identifiants", name="client_identifiants")
     */
    public function identifiants(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
        $changePassword = new ChangePassword();
        $formPassword = $this->createForm(ChangePasswordType::class, $changePassword);
        $formPassword->handleRequest($request);

        if($formPassword->isSubmitted() && $formPassword->isValid()) {
            /** @var Client $client */
            $client = $this->getUser();
            if(!$encoder->isPasswordValid($client, $changePassword->getPassword())) {
                $this->addFlash("warning", "Le mot de passe actuel ne correspond pas.");
            } else {
                $newPassword = $encoder->encodePassword($client, $changePassword->getNewPassword());
                $client->setPassword($newPassword);
                $manager->flush();
                $this->addFlash("success", "Le mot de passe a été modifié.");
            }
        }

        $changeEmail = new ChangeEmail();
        $formEmail = $this->createForm(ChangeEmailType::class, $changeEmail);
        $formEmail->handleRequest($request);

        if($formEmail->isSubmitted() && $formEmail->isValid()) {
            if($manager->getRepository(Client::class)->findOneByEmail($changeEmail->getNewEmail())) {
                $this->addFlash("warning", "L'email est déjà utilisé pour un compte client.");
            } else {
                /** @var Client $client */
                $client = $this->getUser();

                $client->setNewEmail($changeEmail->getNewEmail());
                $client->setActivationKey(uniqid());
                $manager->flush();

                // TODO : Envoyer le mail

                $this->addFlash("success", "Pour valider la modification de l'email, vous devez vérifiez votre adresse. Un mail d'activation vous a été envoyé.");
            }
        }


        return $this->render("Client/client_identifiants.html.twig", [
            "formEmail" => $formEmail->createView(),
            "formPassword" => $formPassword->createView(),
        ]);
    }

    /**
     * @Route("/client/projets/remove/{id}", name="client_projets_remove")
     */
    public function removeClientFromProjet($id, ObjectManager $manager) {
        $clientProjet = $manager->getRepository(ClientProjet::class)->find($id);
        $projet = $clientProjet->getProjet();

        /** @var Client $client */
        $client = $this->getUser();

        if(!$clientProjet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        $projet->removeClientProjet($clientProjet);
        $this->addFlash("success", "Le client a été retiré du projet.");
        $manager->flush();

        return $this->redirectToRoute("client_projets_clients", ["id" => $projet->getId()]);
    }

    /**
     * @Route("/client/projets/{id}", name="client_projets_show")
     */
    public function showProjet($id, ObjectManager $em, Request $request) {
        /** @var Client $client */
        $client = $this->getUser();

        $projet = $em->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        $form = $this->createForm(ProjetEditType::class, $projet);

        return $this->render("Client/projet_show.html.twig", ["projet" => $projet, "form" => $form->createView()]);
    }

    /**
     * @Route("/client/projets/{id}/contacts", name="client_projets_contacts")
     */
    public function showProjetContacts($id, Request $request, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        return $this->render("Client/projet_contacts.html.twig", [
            "projet" => $projet
        ]);
    }

    /**
     * @Route("/client/projet/{id}/contact/{contact_id}", name="client_projets_contacts_show")
     */
    public function showContact($id, $contact_id, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        $contact = $manager->getRepository(Contact::class)->find($contact_id);
        if(!$contact) {
            $this->addFlash("danger", "Ce contact n'existe pas.");
            return $this->redirectToRoute("client_projets_contacts", ["id" => $projet->getId()]);
        }
        if(!$projet->getContacts()->contains($contact)) {
            $this->addFlash("danger", "Ce contact n'existe pas.");
            return $this->redirectToRoute("client_projets_contacts", ["id" => $projet->getId()]);
        }

        return $this->render("Client/projet_contact_show.html.twig", ["projet" => $projet, "contact" => $contact]);
    }

    /**
     * @Route("/client/projets/{id}/documents", name="client_projets_documents")
     */
    public function showProjetDocuments($id, ObjectManager $manager, Request $request) {
        $projet = $manager->getRepository(Projet::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }



        return $this->render("Client/projet_documents.html.twig", [
            "projet" => $projet
        ]);
    }

    /**
     * @Route("/client/documents/{id}/view", name="client_documents_view")
     */
    public function viewDocument($id, ObjectManager $manager, UploaderHelper $helper) {
        $document = $manager->getRepository(Document::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$document) {
            return new Response("<body>Le document n'existe pas.</body>", 404);
        }
        if(!$client->hasAcceptedProjet($document->getProjet())) {
            return new Response("<body>Le document n'existe pas.</body>", 404);
        }


        $file = $helper->asset($document, 'fichierFile');

        return new BinaryFileResponse("../public".$file);
    }

    /**
     * @Route("/client/projets/{id}/entreprise", name="client_projets_entreprise")
     */
    public function showProjetEntreprise($id, ObjectManager $manager, Request $request) {
        $projet = $manager->getRepository(Projet::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }



        return $this->render("Client/projet_entreprise.html.twig", [
            "projet" => $projet
        ]);
    }

    /**
     * @Route("/client/projets/accept/{id}", name="client_projets_accept")
     */
    public function acceptProjet($id, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->ownProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        $client->acceptProjet($projet);
        $manager->flush();

        $this->addFlash("success", "L'invitation au projet a été acceptée.");
        return $this->redirectToRoute("client_home");
    }

    /**
     * @Route("/client/projets/{id}/photos", name="client_projets_photos")
     */
    public function showProjetPhotos($id, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);
        /** @var Client $client */
        $client = $this->getUser();

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }

        if(!$client->hasAcceptedProjet($projet)) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("client_home");
        }



        return $this->render("Client/projet_photos.html.twig", [
            "projet" => $projet
        ]);
    }
}