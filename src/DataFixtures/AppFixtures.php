<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 17:39
 */

namespace App\DataFixtures;


use App\Entity\Client;
use App\Entity\Entreprise;
use App\Entity\Projet;
use App\Repository\EntrepriseRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        //Ajout de $n entreprises
        $n_entreprises = 5;
        for($i = 1; $i <= $n_entreprises; $i++) {
            $entreprise = new Entreprise();
            $entreprise->setEmail("entreprise".$i."@test.housebox.fr");
            $entreprise->setNom("Entreprise ".$i);
            $entreprise->setRoles(["ROLE_USER"]);
            $entreprise->setIsActive(true);
            $entreprise->setActivationKey(uniqid());
            $entreprise->setAdresse("");
            $entreprise->setSiren("000000000");
            $entreprise->setPassword($this->encoder->encodePassword($entreprise, "aaaaaaaa"));
            $manager->persist($entreprise);
        }
        $manager->flush();

        //Ajout de $n clients
        $n_clients = 100;
        for($i = 1; $i <= $n_clients; $i++) {
            $client = new Client();
            $client->setEmail("client".$i."@test.housebox.fr");
            $client->setPassword($this->encoder->encodePassword($client, "aaaaaaaa"));
            $client->setRoles(["ROLE_USER"]);
            $client->setIsActive(true);
            $client->setActivationKey(uniqid());
            $client->setNom("CLIENT ".$i);
            $client->setPrenom("");
            $client->setAdresse("");
            $manager->persist($client);
        }
        $manager->flush();
    }

}