<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 16:02
 */

namespace App\Controller;


use App\Entity\ChangeEmail;
use App\Entity\ChangePassword;
use App\Entity\Client;
use App\Entity\ClientProjet;
use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Entreprise;
use App\Entity\Photo;
use App\Entity\Projet;
use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Form\ContactType;
use App\Form\DocumentEditType;
use App\Form\DocumentType;
use App\Form\EntrepriseProfilType;
use App\Form\PhotoType;
use App\Form\ProjetAddContactType;
use App\Form\ProjetAddType;
use App\Form\ProjetEditType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Vich\UploaderBundle\VichUploaderBundle;

class EntrepriseController extends Controller
{
    /**
     * @Route("/entreprise", name="entreprise_home")
     */
    public function home(Request $request, ObjectManager $manager) {

        $projet = new Projet();
        $form = $this->createForm(ProjetAddType::class, $projet);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $projet->setEntreprise($this->getUser());
            $manager->persist($projet);
            $manager->flush();
            $this->addFlash("success", "Le projet a été créé.");

            unset($projet);
            unset($form);
            $projet = new Projet();
            $form = $this->createForm(ProjetAddType::class, $projet);
        }

        return $this->render("Entreprise/projets.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/entreprise/projets/remove/{id}", name="entreprise_projets_remove")
     */
    public function removeProjet($id, ObjectManager $em) {

        $entreprise = $this->getUser();

        $projet = $em->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() == $entreprise) {
            $em->remove($projet);
            $em->flush();

            $this->addFlash("success", "Le projet a été supprimé.");
        }
        else {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
        }

        return $this->redirectToRoute("entreprise_home");
    }

    /**
     * @Route("/entreprise/projets/{id}", name="entreprise_projets_show")
     */
    public function showProjet($id, ObjectManager $em, Request $request) {
        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();

        $projet = $em->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() != $entreprise) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $form = $this->createForm(ProjetEditType::class, $projet);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash("success", "Le projet a été modifié.");
        }

        return $this->render("Entreprise/projet_show.html.twig", ["projet" => $projet, "form" => $form->createView()]);
    }

    /**
     * @Route("/entreprise/projets/{id}/clients", name="entreprise_projets_clients")
     */
    public function showProjetClients($id, Request $request, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }


         if($request->getMethod() == "POST") {
            if($client_email = $request->request->get("client_email")) {
                $client = $manager->getRepository(Client::class)->findOneByEmail($client_email);
                if(!$client) {
                    // TODO : Ne pas signler que le client n'existe pas et créer un compte client + envoie du mail au client
                    // TODO : Dire dans tous les cas : une invitation a été envoyée au client.
                    $this->addFlash("warning", "Le client n'existe pas.");
                } else {
                    $clientProjet = new ClientProjet();
                    $clientProjet->setClient($client);
                    $clientProjet->setIsAcceptedByClient(false);
                    $projet->addClientProjet($clientProjet);
                    $this->addFlash("success", "Une invitation au projet à été envoyé au client.");
                    $manager->flush();
                }
            }
        }


        return $this->render("Entreprise/projet_clients.html.twig", ["projet" => $projet]);
    }

    /**
     * @Route("/entreprise/projets/{id}/clients/remove/{clientProjet_id}", name="entreprise_projets_clients_remove")
     */
    public function removeClientFromProjet($id, $clientProjet_id, Request $request, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $clientProjet = $manager->getRepository(ClientProjet::class)->find($clientProjet_id);
        if(!$clientProjet) {
            $this->addFlash("warning", "Le client n'existe pas.");
        }


        $projet->removeClientProjet($clientProjet);
        $this->addFlash("success", "Le client a été retiré du projet.");
        $manager->flush();

        return $this->redirectToRoute("entreprise_projets_clients", ["id" => $projet->getId()]);
    }

    /**
     * @Route("/entreprise/projets/{id}/contacts", name="entreprise_projets_contacts")
     */
    public function showProjetContacts($id, Request $request, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($request->getMethod() == "POST") {
            if($contact_id = $request->request->get("contact_id")) {
                $contact = $manager->getRepository(Contact::class)->find($contact_id);
                if(!$contact) {
                    $this->addFlash("warning", "Le contact n'existe pas.");
                } else {
                    if($contact->getEntreprise() != $this->getUser()) {
                        $this->addFlash("warning", "Le contact ne vous appartient pas.");
                    } else {
                        $projet->addContact($contact);
                        $manager->flush();
                    }
                }
            }
        }

        return $this->render("Entreprise/projet_contacts.html.twig", [
            "projet" => $projet
        ]);
    }

    /**
     * @Route("/entreprise/projets/{id}/contacts/remove/{id_contact}", name="entreprise_projets_contacts_remove")
     */
    public function removeContactFromProjet($id, $id_contact, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $contact = $manager->getRepository(Contact::class)->find($id_contact);

        if(!$contact) {
            $this->addFlash("warning", "Ce contact n'existe pas.");
        } else {
            $projet->removeContact($contact);
            $manager->flush();
            $this->addFlash("success", "Le contact a été retiré de ce projet.");
        }

        return $this->redirectToRoute("entreprise_projets_contacts", ["id" => $projet->getId()]);
    }

    /**
     * @Route("/entreprise/projets/{id}/documents", name="entreprise_projets_documents")
     */
    public function showProjetDocuments($id, ObjectManager $manager, Request $request) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $document->setProjet($projet);
            $manager->persist($document);
            $manager->flush();
            $this->addFlash("success", "Le document a été ajouté.");
            unset($document);
            unset($form);
            $document = new Document();
            $form = $this->createForm(DocumentType::class, $document);
        }

        return $this->render("Entreprise/projet_documents.html.twig", [
            "projet" => $projet,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/entreprise/documents/remove/{id}", name="entreprise_documents_remove")
     */
    public function removeDocument($id, ObjectManager $manager) {
        $document = $manager->getRepository(Document::class)->find($id);

        if(!$document) {
            $this->addFlash("warning", "Le document n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();
        /** @var Projet $projet */
        $projet = $document->getProjet();

        if($projet->getEntreprise() != $entreprise) {
            $this->addFlash("warning", "Vous n'êtes pas autorisé à modifier ce document.");
            return $this->redirectToRoute("entreprise_home");
        }

        $manager->remove($document);
        $manager->flush();
        $this->addFlash("success", "Le document a été supprimé.");

        return $this->redirectToRoute("entreprise_projets_documents", ["id" => $projet->getId()]);
    }

    /**
     * @Route("/entreprise/documents/{id}", name="entreprise_documents_edit")
     */
    public function editDocument($id, Request $request, ObjectManager $manager) {
        $document = $manager->getRepository(Document::class)->find($id);

        if(!$document) {
            $this->addFlash("warning", "Le document n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();
        /** @var Projet $projet */
        $projet = $document->getProjet();

        if($projet->getEntreprise() != $entreprise) {
            $this->addFlash("warning", "Vous n'êtes pas autorisé à modifier ce document.");
            return $this->redirectToRoute("entreprise_home");
        }

        $form = $this->createForm(DocumentEditType::class, $document);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", "Le document a été supprimé.");
            return $this->redirectToRoute("entreprise_projets_documents", ["id" => $projet->getId()]);
        }

        return $this->render("Entreprise/projet_document_edit.html.twig", ["form" => $form->createView(), "projet" => $projet]);
    }

    /**
     * @Route("/entreprise/documents/{id}/view", name="entreprise_documents_view")
     */
    public function viewDocument($id, ObjectManager $manager, UploaderHelper $helper) {
        $document = $manager->getRepository(Document::class)->find($id);

        if(!$document) {
            return new Response("<body>Le document n'existe pas.</body>", 404);
        }
        if($document->getProjet()->getEntreprise() != $this->getUser()) {
            return new Response("<body>Le document n'existe pas.</body>", 404);
        }


        $file = $helper->asset($document, 'fichierFile');

        return new BinaryFileResponse("../public".$file);
    }

    /**
     * @Route("/entreprise/projets/{id}/photos", name="entreprise_projets_photos")
     */
    public function showPhotosProjet($id, Request $request, ObjectManager $manager) {
        $projet = $manager->getRepository(Projet::class)->find($id);

        if(!$projet) {
            $this->addFlash("danger", "Ce projet n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $photo->setProjet($projet);
            $manager->persist($photo);
            $manager->flush();

            $this->addFlash("success", "La photo a été ajoutée.");

            unset($form);
            unset($photo);
            $photo = new Photo();
            $form = $this->createForm(PhotoType::class, $photo);
        }


        return $this->render("Entreprise/projet_photos.html.twig", [
            "projet" => $projet,
            "form" => $form->createView()
        ]);

    }

    /**
     * @Route("/entreprise/photo/{id}/remove", name="entreprise_projets_photos_remove")
     */
    public function removePhotoFromProjet($id, ObjectManager $manager) {
        $photo = $manager->getRepository(Photo::class)->find($id);

        if(!$photo) {
            $this->addFlash("warning", "La photo n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        if($photo->getProjet()->getEntreprise() != $this->getUser()) {
            $this->addFlash("warning", "La photo n'existe pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        $manager->remove($photo);
        $manager->flush();

        $this->addFlash("success", "La photo a été supprimé.");
        return $this->redirectToRoute("entreprise_projets_photos", ["id" => $photo->getProjet()->getId()]);
    }


    /**
     * @Route("/entreprise/profil", name="entreprise_profil")
     */
    public function showProfil(Request $request, ObjectManager $manager) {

        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();

        $form = $this->createForm(EntrepriseProfilType::class, $entreprise);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", "Le profil société a été enregistré.");
        }

        return $this->render("Entreprise/entreprise_profil.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/entreprise/profil/removeLogo", name="entreprise_profil_remove_logo")
     */
    public function removeLogo(ObjectManager $manager, UploaderHelper $helper, Filesystem $filesystem) {
        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();

        $file = $helper->asset($entreprise, 'logoFile');
        $path = "../public".$file;
        $filesystem->remove($path);

        $entreprise->setLogo(null);
        $this->addFlash("success", "Le logo a été supprimé.");
        $manager->flush();


        return $this->redirectToRoute("entreprise_profil");
    }

    /**
     * @Route("/entreprise/contacts", name="entreprise_contacts")
     */
    public function contactsList() {
        /** @var Entreprise $entreprise */
        $entreprise = $this->getUser();

        return $this->render("Entreprise/entreprise_contacts.html.twig", [
            "contacts" => $entreprise->getContacts()
        ]);
    }

    /**
     * @Route("/entreprise/contacts/show", name="entreprise_contacts_show")
     */
    public function addOrEditContact(Request $request, ObjectManager $manager) {
        // TODO
    }

    /**
     * @Route("/entreprise/contacts/remove/{id}", name="entreprise_contacts_remove")
     */
    public function removeContact($id, ObjectManager $manager) {
        $contact = $manager->getRepository(Contact::class)->find($id);

        if(!$contact) {
            $this->addFlash("warning", "Le contact n'existe pas.");
        } else {
            if($contact->getEntreprise() != $this->getUser()) {
                $this->addFlash("warning", "Vous n'avez pas la permission de modifier ce contact.");
            } else {
                $manager->remove($contact);
                $manager->flush();
                $this->addFlash("success", "Le contact a été supprimé.");
            }
        }

        return $this->redirectToRoute("entreprise_contacts");
    }

    /**
     * @Route("entreprise/contacts/edit/{id}", name="entreprise_contacts_edit")
     */
    public function editContact($id, Request $request, ObjectManager $manager) {
        $contact = $manager->getRepository(Contact::class)->find($id);


        if(!$contact) {
            $this->addFlash("warning", "Erreur : Le contact n'existe pas.");
            return $this->redirectToRoute("entreprise_contacts");
        }
        if($contact->getEntreprise() != $this->getUser()) {
            $this->addFlash("warning", "Erreur : Le contact n'existe pas.");
            return $this->redirectToRoute("entreprise_contacts");
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", "Le contact a été modifié.");
            return $this->redirectToRoute("entreprise_contacts");
        }

        return $this->render("Entreprise/contact_edit.html.twig", ["form" => $form->createView(), "action" => "Modifier"]);
    }

    /**
     * @Route("entreprise/contacts/add", name="entreprise_contacts_add")
     */
    public function addContact(Request $request, ObjectManager $manager) {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $contact->setEntreprise($this->getUser());
            $manager->persist($contact);
            $manager->flush();
            $this->addFlash("success", "Le contact a été ajouté.");
            return $this->redirectToRoute("entreprise_contacts");
        }

        return $this->render("Entreprise/contact_edit.html.twig", ["form" => $form->createView(), "action" => "Ajouter"]);
    }

    /**
     * @Route("/entreprise/identifiants", name="entreprise_identifiants")
     */
    public function identifiants(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {

        $changePassword = new ChangePassword();
        $formPassword = $this->createForm(ChangePasswordType::class, $changePassword);
        $formPassword->handleRequest($request);

        if($formPassword->isSubmitted() && $formPassword->isValid()) {
            /** @var Entreprise $entreprise */
            $entreprise = $this->getUser();
            if(!$encoder->isPasswordValid($entreprise, $changePassword->getPassword())) {
                $this->addFlash("warning", "Le mot de passe actuel ne correspond pas.");
            } else {
                $newPassword = $encoder->encodePassword($entreprise, $changePassword->getNewPassword());
                $entreprise->setPassword($newPassword);
                $manager->flush();
                $this->addFlash("success", "Le mot de passe a été modifié.");
            }
        }

        $changeEmail = new ChangeEmail();
        $formEmail = $this->createForm(ChangeEmailType::class, $changeEmail);
        $formEmail->handleRequest($request);

        if($formEmail->isSubmitted() && $formEmail->isValid()) {
           if($manager->getRepository(Entreprise::class)->findOneByEmail($changeEmail->getNewEmail())) {
               $this->addFlash("warning", "L'email est déjà utilisé pour un compte entreprise.");
           } else {
               /** @var Entreprise $entreprise */
               $entreprise = $this->getUser();

               $entreprise->setNewEmail($changeEmail->getNewEmail());
               $entreprise->setActivationKey(uniqid());
               $manager->flush();

               // TODO : Envoyer le mail

               $this->addFlash("success", "Pour valider la modification de l'email, vous devez vérifiez votre adresse. Un mail d'activation vous a été envoyé.");
           }
        }


        return $this->render("Entreprise/entreprise_identifiants.html.twig", [
            "formEmail" => $formEmail->createView(),
            "formPassword" => $formPassword->createView(),
        ]);
    }
}