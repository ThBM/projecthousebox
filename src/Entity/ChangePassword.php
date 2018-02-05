<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 30/01/2018
 * Time: 18:58
 */

namespace App\Entity;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


class ChangePassword
{
    private $password;

    /**
     * @Assert\Length(min=8, minMessage="Le mot de passe doit contenir au moins 8 caractères.")
     */
    private $newPassword;

    /**
     * @return mixed
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param mixed $newPassword
     */
    public function setNewPassword($newPassword): void
    {
        $this->newPassword = $newPassword;
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

}