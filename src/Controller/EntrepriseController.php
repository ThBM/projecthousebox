<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 16:02
 */

namespace App\Controller;


use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EntrepriseController extends Controller
{
    /**
     * @Route("/entreprise", name="entreprise_home")
     */
    public function home() {
        return $this->render("Entreprise/projets.html.twig");
    }

    /**
     * @Route("/entreprise/projets/add", name="entreprise_projets_add")
     */
    public function addProject(Request $request) {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $projet->setEntreprise($this->getUser());
            $em = $this->getDoctrine()->getManager();

            $em->persist($projet);
            $em->flush();

            $this->addFlash("success", "Le projet a bien été créé.");
            return $this->redirectToRoute("entreprise_home");
        }


        return $this->render("projet_add.html.twig", [
           "form" => $form->createView()
        ]);
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
    public function showProjet($id, ObjectManager $em) {
        $projet = $em->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        return $this->render("Entreprise/projet_show.html.twig", ["projet" => $projet]);
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
    public function showProjetDocuments($id, ObjectManager $em) {
        $projet = $em->getRepository(Projet::class)->find($id);

        if($projet->getEntreprise() != $this->getUser()) {
            $this->addFlash("danger", "Ce projet ne vous appartient pas.");
            return $this->redirectToRoute("entreprise_home");
        }

        return $this->render("Entreprise/projet_documents.html.twig", ["projet" => $projet]);
    }

}