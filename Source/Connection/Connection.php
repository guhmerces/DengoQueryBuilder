<?php

namespace Source\Connection;

class Connection
{    
    const OPTIONS = [
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
    ];

    private static $instance;

    public static function getConnection(): ?\PDO
    {
        try {
            if (empty(self::$instance)) {
                self::$instance = new \PDO(
                    'mysql:host=' . DATABASE_HOST . ';port='. DATABASE_PORT .';dbname=' . DATABASE_NAME,
                    DATABASE_USER,
                    DATABASE_PASSWD,
                    self::OPTIONS
                );

                return self::$instance;

            } else {
                return self::$instance;
            }

        } catch (\PDOException $e) {
            return null;
        }
    }

    final private function __construct()
    {

    }

    final private function __clone()
    {

    }

    final public function __wakeup()
    {

    }
}