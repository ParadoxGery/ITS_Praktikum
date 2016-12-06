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

    public function __construct($username, $password, $role)
    {
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    public function getRoles() {
        return $this->role;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getSalt() {
        return $this->password;
    }

    public function getUsername() {
        return $this->username;
    }

    public function eraseCredentials() {
        //
    }
}