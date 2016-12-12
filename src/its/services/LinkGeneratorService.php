<?php


namespace its\services;


use Doctrine\DBAL\Driver\Connection;

class LinkGeneratorService
{
    private $connection;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    public function generateMailLink($uid){
        $date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        $code = str_replace('/','',stripslashes(password_hash(date("Y-m-d H:i:s"),PASSWORD_BCRYPT)));

        $this->connection->insert('mailcodes', array(
            'code' => $code,
            'used' => 0,
            'expires' => $date,
            'uid' => $uid,
        ));
    }

    public function generatePasswordRecoveryLink($uid){
        $date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        $code = str_replace('/','',stripslashes(password_hash(date("Y-m-d H:i:s"),PASSWORD_BCRYPT)));

        $this->connection->insert('recoverycodes',array(
            'code' => $code,
            'used' => 0,
            'expires' => $date,
            'uid' => $uid,
        ));
    }
}