<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
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
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fonction;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="L'email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex("/^[0-9]{10}$/", message="Le numéro de téléphone n'est pas valide.")
     */
    private $telephone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Entreprise", inversedBy="contacts")
     */
    private $entreprise;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Projet", inversedBy="contacts")
     */
    private $projets;

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
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param mixed $prenom
     */
    public function setPrenom($prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @return mixed
     */
    public function getFonction()
    {
        return $this->fonction;
    }

    /**
     * @param mixed $fonction
     */
    public function setFonction($fonction): void
    {
        $this->fonction = $fonction;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $telephone
     */
    public function setTelephone($telephone): void
    {
        $this->telephone = $telephone;
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
    public function getProjets()
    {
        return $this->projets;
    }

    /**
     * @param mixed $projets
     */
    public function setProjets($projets): void
    {
        $this->projets = $projets;
    }

    public function addProjet(Projet $projet) {
        if ($this->projets->contains($projet)) {
            return;
        }

        $this->projets[] = $projet;
        $projet->addContact($this);
    }

    public function removeProjet(Projet $projet) {
        if (!$this->projets->contains($projet)) {
            return;
        }

        $this->projets->removeElement($projet);
        $projet->removeContact($this);
    }
}
