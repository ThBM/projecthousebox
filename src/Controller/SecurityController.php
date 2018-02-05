<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 18:14
 */

namespace App\Controller;


use App\Entity\Client;
use App\Entity\Entreprise;
use App\Form\ClientRegisterType;
use App\Form\EntrepriseRegisterType;
use App\Repository\EntrepriseRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login/entreprise", name="security_login_entreprise")
     */
    public function loginEntreprise(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('Entreprise/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register/entreprise", name="security_register_entreprise")
     */
    public function registerEntreprise(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer) {

        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseRegisterType::class, $entreprise);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            if($em->getRepository(Entreprise::class)->findByEmail($entreprise->getEmail())) {
                $this->addFlash("warning", "Cet email est déjà utilisé. Vous pouvez vous connecter.");
                return $this->redirectToRoute("security_login_entreprise");
            }

            $entreprise->setIsActive(false);
            $entreprise->setRoles(["ROLE_USER"]);

            $entreprise->setPassword($encoder->encodePassword($entreprise, $entreprise->getPassword()));

            $entreprise->setActivationKey(uniqid());

            $em->persist($entreprise);
            $em->flush();


            // TODO : Faire fonctionner le mail
            $mail = new \Swift_Message("Housebox : Activez votre compte");
            $mail->setFrom("thibault.barolatmassole@gmail.com");
            $mail->setTo("thibault.bm@icloud.com");
            $mail->setBody("Activation key : ".$entreprise->getActivationKey());

            if($a = $mailer->send($mail))
                $this->addFlash("success", "Email ok. TODO : check if email is sent. n=".$a);


            $this->addFlash("success", "Votre compte a été créé. Un email vous a été envoyé pour activer votre compte.");
            return $this->redirectToRoute("security_login_entreprise");
        }

        return $this->render("Entreprise/register.html.twig", ["form" => $form->createView()]);
    }


    /**
     * @Route("/activation/entreprise/{activationKey}")
     */
    public function activateEntreprise($activationKey, ObjectManager $em) {
        if($activationKey == "") {
            $this->addFlash("warning", "Le code d'activation est invalide.");
        } else {
            $entreprise = $em->getRepository(Entreprise::class)->findOneBy(["activationKey" => $activationKey]);

            if(!$entreprise) {
                $this->addFlash("warning", "Ce compte n'existe pas.");
            } else {
                $entreprise->setIsActive(true);
                $entreprise->setActivationKey("");
                if($entreprise->getNewEmail() != "") {
                    $entreprise->setEmail($entreprise->getNewEmail());
                    $entreprise->setNewEmail("");
                    $this->addFlash("success", "Vous pouvez utiliser votre nouvel email pour vous connecter.");
                }
                $em->flush();

                $this->addFlash("success", "Votre compte a été activé. Vous pouvez vous connecter.");
            }
        }

        return $this->redirectToRoute("security_login_entreprise");
    }


    /**
     * @Route("/login/client", name="security_login_client")
     */
    public function loginClient(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('Client/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register/client", name="security_register_client")
     */
    public function registerClient(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer) {
        $client = new Client();
        $form = $this->createForm(ClientRegisterType::class, $client);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            if($em->getRepository(Client::class)->findByEmail($client->getEmail())) {
                $this->addFlash("warning", "Cet email est déjà utilisé. Vous pouvez vous connecter.");
                return $this->redirectToRoute("security_login_client");
            }

            $client->setIsActive(false);
            $client->setRoles(["ROLE_USER"]);

            $client->setPassword($encoder->encodePassword($client, $client->getPassword()));

            $client->setActivationKey(uniqid());

            $em->persist($client);
            $em->flush();


            // TODO : Faire fonctionner le mail
            $mail = new \Swift_Message("Housebox : Activez votre compte");
            $mail->setFrom("thibault.barolatmassole@gmail.com");
            $mail->setTo("thibault.barolatmassole@gmail.com");
            $mail->setBody("Activation key : ".$client->getActivationKey());

            if($a = $mailer->send($mail))
                $this->addFlash("success", "Email ok. TODO : check if email is sent. n=".$a);


            $this->addFlash("success", "Votre compte a été créé. Un email vous a été envoyé pour activer votre compte.");
            return $this->redirectToRoute("security_login_client");
        }

        return $this->render("Client/register.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/activation/client/{activationKey}")
     */
    public function activateClient($activationKey, ObjectManager $em) {
        if($activationKey == "") {
            $this->addFlash("warning", "Le code d'activation est invalide.");
        } else {
            $client = $em->getRepository(Client::class)->findOneBy(["activationKey" => $activationKey]);

            if(!$client) {
                $this->addFlash("warning", "Ce compte n'existe pas.");
            } else {
                $client->setIsActive(true);
                $client->setActivationKey("");
                if($client->getNewEmail() != "") {
                    $client->setEmail($client->getNewEmail());
                    $client->setNewEmail("");
                    $this->addFlash("success", "Vous pouvez utiliser votre nouvel email pour vous connecter.");
                }
                $em->flush();

                $this->addFlash("success", "Votre compte a été activé. Vous pouvez vous connecter.");
            }
        }

        return $this->redirectToRoute("security_login_client");
    }

    //TODO : Changmeent de mot de passe
}