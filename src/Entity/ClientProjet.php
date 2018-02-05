<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientProjetRepository")
 */
class ClientProjet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="clientProjet")
     */
    private $client;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Projet", inversedBy="clientProjet")
     */
    private $projet;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAcceptedByClient;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getProjet()
    {
        return $this->projet;
    }

    /**
     * @param mixed $projet
     */
    public function setProjet($projet): void
    {
        $this->projet = $projet;
    }

    /**
     * @return mixed
     */
    public function getisAcceptedByClient()
    {
        return $this->isAcceptedByClient;
    }

    /**
     * @param mixed $isAcceptedByClient
     */
    public function setIsAcceptedByClient($isAcceptedByClient): void
    {
        $this->isAcceptedByClient = $isAcceptedByClient;
    }

}
