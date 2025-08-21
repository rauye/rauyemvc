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

    public static function getObjAll($query, $bindArr = [])
    {
        $rows = self::getAll($query, $bindArr);
        $obj = [];
        foreach ($rows as $row) {
            $std = new \stdClass();
            foreach ($row as $k => $v) {
                $std->$k = $v;
            }
            $obj[] = $std;
        }
        return $obj;
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

    public static function getObjFirst($query, $bindArr = [])
    {
        $row = self::getFirst($query, $bindArr);
        if (is_null($row) or isset($row->scalar)) {
            return null;
        }
        $std = new \stdClass();
        foreach ($row as $k => $v) {
            $std->$k = $v;
        }
        return $std;
    }
}