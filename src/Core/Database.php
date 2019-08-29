<?php

namespace Z2Admin\Core;

class Database
{
    /**
     * @var \PDO
     */
    private static $conn;

    public static function getConn()
    {
        if (is_null(self::$conn)) {
            try {
                $conn = new \PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
                $conn->query("SET NAMES 'utf8'");
                $conn->query('SET character_set_connection=utf8');
                $conn->query('SET character_set_client=utf8');
                $conn->query('SET character_set_results=utf8');
                $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$conn = $conn;

            } catch(\PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        }
        return self::$conn;
    }


}