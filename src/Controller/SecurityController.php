<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 18:14
 */

namespace App\Controller;


use App\Entity\Entreprise;
use App\Form\EntrepriseRegisterType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login/entreprise", name="security_login_entreprise")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
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
    public function register(Request $request, ObjectManager $em, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer) {

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

            $em->persist($entreprise);
            $em->flush();

            $mail = new \Swift_Message("Housebox : Activez votre compte");
            $mail->setFrom("thibault.barolatmassole@gmail.com");
            $mail->setTo("thibault.bm@icloud.com");
            $mail->setBody("Bonjour");

            if($a = $mailer->send($mail))
                $this->addFlash("success", "Email ok. TODO : check if email is sent. n=".$a);

            $this->addFlash("success", "Votre compte a été créé. Un email vous a été envoyé pour activer votre compte.");
            return $this->redirectToRoute("security_login_entreprise");
        }

        return $this->render("Entreprise/register.html.twig", ["form" => $form->createView()]);
    }


}