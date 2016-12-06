<?php
/**
 * Created by PhpStorm.
 * User: marc
 * Date: 06.12.16
 * Time: 15:49
 */

namespace its\user;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserPasswordEncoder implements UserPasswordEncoderInterface
{
    public function encodePassword(UserInterface $user, $plainpassword) {
        return password_hash($plainpassword, PASSWORD_DEFAULT);
    }

    public function isPasswordValid(UserInterface $user, $raw) {
        return password_verify($raw, $user->getPassword());
    }
}