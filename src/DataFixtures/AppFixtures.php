<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 24/01/2018
 * Time: 17:39
 */

namespace App\DataFixtures;


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
        /*for($i =0 ; $i < 5; $i++) {
            $entreprise = new Entreprise();
            $entreprise->setEmail("entreprise".$i."@gmail.com");
            $entreprise->setIsActive(true);
            $entreprise->setRoles(["ROLE_USER"]);

            $password = "aaaaaaaa";
            $entreprise->setPassword($this->encoder->encodePassword($entreprise, $password));

            $manager->persist($entreprise);
        }


        for($i = 1; $i < 100; $i++) {
            $entreprise = $manager->getRepository(EntrepriseRepository::class)->find(rand(1,5));
            $projet = new Projet();
            $projet->setNom(chr($i));
            $projet->setEntreprise($entreprise);
            $manager->persist($projet);
        }

        $manager->flush();*/
    }
}