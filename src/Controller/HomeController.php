<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 15:49
 */

namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage() {
        return $this->render("home.html.twig");
    }
}