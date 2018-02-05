<?php
/**
 * Created by PhpStorm.
 * User: TBarolat
 * Date: 30/01/2018
 * Time: 20:12
 */

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ChangeEmail
{
    /**
     * @Assert\Email(message="L'email n'est pas valide.")
     */
    private $newEmail;

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


}