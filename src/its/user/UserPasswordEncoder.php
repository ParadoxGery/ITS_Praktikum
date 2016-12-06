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
    public function encodePassword(UserInterface $user, string $plainpassword) {
        return password_hash($plainpassword, PASSWORD_DEFAULT);
    }

    public function isPasswordValid(UserInterface $user, string $raw) {
        return password_verify($raw, $user->getPassword());
    }
}