<?php


namespace its\services;


use Doctrine\DBAL\Driver\Connection;

class MailLinkGeneratorService
{
    private $connection;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    public function generateMailLink($uid){
        $date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        $link = stripslashes(password_hash(date("Y-m-d H:i:s"),PASSWORD_BCRYPT));

        $this->connection->insert('mailcodes', array(
            'link' => $link,
            'used' => 0,
            'expires' => $date,
            'uid' => $uid,
        ));
    }
}