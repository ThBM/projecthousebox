<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=300, unique=true)
     * @Assert\Email(message="L'email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\Length(min=8, minMessage="Le mot de passe doit contenir au moins 8 caractères.")
     */
    private $password;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $activationKey;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="text")
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Assert\Email()
     */
    private $newEmail;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClientProjet", mappedBy="client", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $clientProjet;

    public function eraseCredentials()
    {

    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }


    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isActive;
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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getisActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getActivationKey()
    {
        return $this->activationKey;
    }

    /**
     * @param mixed $activationKey
     */
    public function setActivationKey($activationKey): void
    {
        $this->activationKey = $activationKey;
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
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param mixed $adresse
     */
    public function setAdresse($adresse): void
    {
        $this->adresse = $adresse;
    }

    /**
     * @return mixed
     */
    public function getNewEmail()
    {
        return $this->newEmail;
    }

    /**
     * @param mixed $newEmail
     */
    public function setNewEmail($newEmail): void
    {
        $this->newEmail = $newEmail;
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


    public function ownProjet(Projet $projet) {
        foreach($this->clientProjet as $item) {
            if($item->getProjet() === $projet) {
                return true;
            }
        }
        return false;
    }

    public function hasAcceptedProjet(Projet $projet) {
        /** @var ClientProjet $item */
        foreach($this->clientProjet as $item) {
            if($item->getProjet() === $projet) {
                return $item->getisAcceptedByClient();
            }
        }
        return false;
    }

    public function acceptProjet(Projet $projet) {
        foreach($this->clientProjet as $item) {
            if($item->getProjet() === $projet) {
                $item->setIsAcceptedByClient(true);
                return true;
            }
        }
        return false;
    }
}
