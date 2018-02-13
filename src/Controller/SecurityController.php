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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login/entreprise", name="security_login_entreprise")
     */
    public function loginEntreprise(Request $request, AuthenticationUtils $authUtils, ObjectManager $manager, SessionInterface $session)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        //Gérer les erreurs de login
        if($error) {
            switch ($error->getMessageKey()) {
                case "Invalid credentials." :
                    $this->addFlash("warning", "L'Email / mot de passe est incorrect.");

                    $failed_login = $session->get("failed_login");
                    $failed_login = $failed_login ? $failed_login+1 : 1;
                    $session->set("failed_login", $failed_login);

                    if($failed_login >= 5) {
                        $entreprise = $manager->getRepository(Entreprise::class)->findOneByEmail($lastUsername);
                        if ($entreprise) {
                            $lockedUntil = new \DateTime();
                            $lockedUntil->add(new \DateInterval("PT10M"));
                            $entreprise->setLockedUntilTime($lockedUntil);
                            $manager->flush();
                        }
                        $session->set("failed_login", 0);
                    }
                    break;
                case "Account is locked." :
                    $entreprise = $manager->getRepository(Entreprise::class)->findOneByEmail($lastUsername);
                    if($entreprise) {
                        $this->addFlash("warning", "Le compte est bloqué jusque ".$entreprise->getLockedUntilTime()->format("H:i").".");
                    }
                    break;
                case "Account is disabled." :
                    $this->addFlash("warning", "Ce compte n'a pas été activé. Vérifiez vos emails, un email vous a été transmis pour l'activation.");
                    break;
                default:
                    $this->addFlash("warning", "Une erreur s'est produite.");
            }
        }

        return $this->render('Entreprise/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register/entreprise", name="security_register_entreprise")
     */
    public function registerEntreprise(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, UrlGeneratorInterface $router) {

        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseRegisterType::class, $entreprise);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            if($em->getRepository(Entreprise::class)->findByEmail($entreprise->getEmail())) {
                $this->addFlash("warning", "Cet email est déjà utilisé.");
                return $this->redirectToRoute("security_login_entreprise");
            }

            $entreprise->setIsActive(false);
            $entreprise->setRoles(["ROLE_USER"]);

            $entreprise->setPassword($encoder->encodePassword($entreprise, $entreprise->getPassword()));

            $entreprise->setActivationKey(uniqid());

            $em->persist($entreprise);
            $em->flush();


            $mail = new \Swift_Message("Housebox : Activez votre compte");
            $mail->setFrom("contact@housebox.fr");
            $mail->setTo($entreprise->getEmail());
            $mail->setBcc("contact@housebox.fr");
            $mail->setBody(
                $this->renderView("Emails/registration.html.twig", [
                    "url" => $router->generate("security_activation_entreprise", ["activationKey" => $entreprise->getActivationKey()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]),
                "text/html"
            );
            $mail->addPart(
                $this->renderView("Emails/registration.txt.twig", [
                    "url" => $router->generate("security_activation_entreprise", ["activationKey" => $entreprise->getActivationKey()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]),
                "text/plain"
            );

            $mailer->send($mail);


            $this->addFlash("success", "Votre compte a été créé. Un email vous a été envoyé pour activer votre compte.");
            return $this->redirectToRoute("security_login_entreprise");
        }

        return $this->render("Entreprise/register.html.twig", ["form" => $form->createView()]);
    }


    /**
     * @Route("/activation/entreprise/{activationKey}", name="security_activation_entreprise")
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
    public function loginClient(Request $request, AuthenticationUtils $authUtils, ObjectManager $manager, SessionInterface $session)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();


        //Gérer les erreurs de login
        if($error) {
            switch ($error->getMessageKey()) {
                case "Invalid credentials." :
                    $this->addFlash("warning", "L'Email / mot de passe est incorrect.");

                    $failed_login = $session->get("failed_login");
                    $failed_login = $failed_login ? $failed_login+1 : 1;
                    $session->set("failed_login", $failed_login);

                    if($failed_login >= 5) {
                        $client = $manager->getRepository(Client::class)->findOneByEmail($lastUsername);
                        if ($client) {
                            $lockedUntil = new \DateTime();
                            $lockedUntil->add(new \DateInterval("PT10M"));
                            $client->setLockedUntilTime($lockedUntil);
                            $manager->flush();
                        }
                        $session->set("failed_login", 0);
                    }
                    break;
                case "Account is locked." :
                    $client = $manager->getRepository(Client::class)->findOneByEmail($lastUsername);
                    if($client) {
                        $this->addFlash("warning", "Le compte est bloqué jusque ".$client->getLockedUntilTime()->format("H:i").".");
                    }
                    break;
                case "Account is disabled." :
                    $this->addFlash("warning", "Ce compte n'a pas été activé. Vérifiez vos emails, un email vous a été transmis pour l'activation.");
                    break;
                default:
                    $this->addFlash("warning", "Une erreur s'est produite.");
            }
        }

        return $this->render('Client/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register/client", name="security_register_client")
     */
    public function registerClient(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, UrlGeneratorInterface $router) {
        $client = new Client();
        $form = $this->createForm(ClientRegisterType::class, $client);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            if($em->getRepository(Client::class)->findByEmail($client->getEmail())) {
                $this->addFlash("warning", "Cet email est déjà utilisé.");
                return $this->redirectToRoute("security_login_client");
            }

            $client->setIsActive(false);
            $client->setRoles(["ROLE_USER"]);

            $client->setPassword($encoder->encodePassword($client, $client->getPassword()));

            $client->setActivationKey(uniqid());

            $em->persist($client);
            $em->flush();

            $mail = new \Swift_Message("Housebox : Activez votre compte");
            $mail->setFrom("contact@housebox.fr");
            $mail->setTo($client->getEmail());
            $mail->setBcc("contact@housebox.fr");
            $mail->setBody(
                $this->renderView("Emails/registration.html.twig", [
                    "url" => $router->generate("security_activation_client", ["activationKey" => $client->getActivationKey()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]),
                "text/html"
            );
            $mail->addPart(
                $this->renderView("Emails/registration.txt.twig", [
                    "url" => $router->generate("security_activation_client", ["activationKey" => $client->getActivationKey()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]),
                "text/plain"
            );

            $mailer->send($mail);

            $this->addFlash("success", "Votre compte a été créé. Un email vous a été envoyé pour activer votre compte.");
            return $this->redirectToRoute("security_login_client");
        }

        return $this->render("Client/register.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/activation/client/{activationKey}", name="security_activation_client")
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