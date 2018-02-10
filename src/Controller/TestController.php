<?php
/**
 * Created by PhpStorm.
 * User: ThibaultB
 * Date: 09/02/2018
 * Time: 19:04
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends Controller
{
    /**
     * @Route("/test/mail", name="test_mail")
     */
    public function mail(\Swift_Mailer $mailer) {

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('thibault.barolatmassole@gmail.com')
            ->setBody(
                $this->renderView("Emails/registration.html.twig", ["url" => "http://housebox.fr"]),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        //$mailer->send($message);

        return new Response("OKz");
    }
}