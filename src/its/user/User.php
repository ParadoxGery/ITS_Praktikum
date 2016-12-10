<?php
/**
 * Created by PhpStorm.
 * User: marc
 * Date: 06.12.16
 * Time: 15:23
 */

namespace its\user;

use Symfony\Component\Security\Core\User\UserInterface;


class User implements UserInterface
{
    private $username;
    private $password;
    private $role;
    private $mail;

    public function __construct($username, $password, $role, $mail)
    {
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
        $this->mail = $mail;
    }

    public function getRoles()
    {
        return $this->role;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->password;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getMail()
    {
        return $this->mail;
    }



    public function eraseCredentials()
    {
        //
    }
}