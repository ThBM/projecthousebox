<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 16:02
 */

namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Entreprise extends Controller
{
    /**
     * @Route("/entreprise", name="entreprise_home")
     */
    public function home() {
        return new Response("THB : controller not setup.");
    }

    /**
     * @Route("/entreprise/login", name="entreprise_login")
     */
    public function login() {
        return $this->render("Entreprise/login.html.twig");
    }
}