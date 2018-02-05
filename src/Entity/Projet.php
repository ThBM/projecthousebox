<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjetRepository")
 */
class Projet
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Entreprise", inversedBy="projets")
     */
    private $entreprise;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $statut;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="projet", cascade={"persist", "remove"})
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Photo", mappedBy="projet", cascade={"persist", "remove"})
     */
    private $photos;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Contact", mappedBy="projets")
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClientProjet", mappedBy="projet", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $clientProjet;

    /**
     * Projet constructor.
     * @param $date
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->statut = "Ouvert";
    }

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
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getEntreprise()
    {
        return $this->entreprise;
    }

    /**
     * @param mixed $entreprise
     */
    public function setEntreprise($entreprise): void
    {
        $this->entreprise = $entreprise;
    }

    /**
     * @return mixed
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * @param mixed $statut
     */
    public function setStatut($statut): void
    {
        $this->statut = $statut;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }


    public function getDocuments()
    {
        return $this->documents;
    }


    public function setDocuments($documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param mixed $photos
     */
    public function setPhotos($photos): void
    {
        $this->photos = $photos;
    }


    /**
     * @return mixed
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param mixed $contacts
     */
    public function setContacts($contacts): void
    {
        $this->contacts = $contacts;
    }

    public function addContact(Contact $contact) {
        if ($this->contacts->contains($contact)) {
            return;
        }

        $this->contacts[] = $contact;
        $contact->addProjet($this);
    }


    public function removeContact(Contact $contact) {
        if (!$this->contacts->contains($contact)) {
            return;
        }
        $this->contacts->removeElement($contact);
        $contact->removeProjet($this);
    }



    /**
     * @return mixed
     */
    public function getClientProjet()
    {
        return $this->clientProjet;
    }

    /**
     * @param mixed $clientProjet
     */
    public function setClientProjet($clientProjet): void
    {
        $this->clientProjet = $clientProjet;
    }

    public function addClientProjet(ClientProjet $clientProjet) {
        $this->clientProjet[] = $clientProjet;
        $clientProjet->setProjet($this);
    }

    public function removeClientProjet(ClientProjet $clientProjet) {
        $this->clientProjet->removeElement($clientProjet);
        $clientProjet->setProjet(null);
    }
}
