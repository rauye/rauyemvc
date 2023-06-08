<?php

namespace RauyeMVC\Support;

class Conn
{
    public static function getAll($query, $bindArr = [])
    {
        $conn = Database::getConn();
        $stmt = $conn->prepare($query);
        if (is_string($bindArr)) {
            $stmt->execute([$bindArr]);
        } else {
            $stmt->execute($bindArr);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getFirst($query, $bindArr = [])
    {
        $conn = Database::getConn();
        $stmt = $conn->prepare($query);
        if (is_string($bindArr)) {
            $stmt->execute([$bindArr]);
        } else {
            $stmt->execute($bindArr);
        }
        $stmt->execute();
        return (object) $stmt->fetch();
    }
}