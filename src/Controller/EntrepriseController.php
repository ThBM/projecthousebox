<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 16:02
 */

namespace App\Controller;


use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Entreprise;
use App\Entity\Projet;
use App\Form\ContactType;
use App\Form\DocumentType;
use App\Form\EntrepriseProfilType;
use App\Form\ProjetAddType;
use App\Form\ProjetEditType;
use App\Repository\ProjetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function showProjetClients($id, ObjectManager $em) {
        $projet = $em->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        return $this->render("Entreprise/projet_clients.html.twig", ["projet" => $projet]);
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

        $form = $this->createForm(DocumentType::class, $document);



        $this->addFlash("success", "Le document a été supprimé.");

        return $this->redirectToRoute("entreprise_projets_documents", ["id" => $projet->getId()]);
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
}