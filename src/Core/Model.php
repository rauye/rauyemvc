<?php

namespace RauyeMVC\Core;

use Cake\Utility\Inflector;

class Model
{
    protected $_table = null;
    protected static $_database;

    public function __construct()
    {
        self::$_database = new Database();
        $this->setTableName();
        $this->id = null;
    }

    private function setTableName()
    {
        if (is_null($this->_table)) {
            $parts = explode('\\', get_called_class());
            $className = $parts[sizeof($parts) - 1];
            $this->_table = Inflector::underscore($className);
        }
    }

    protected static function getDatabase()
    {
        if (is_null(self::$_database)) {
            self::$_database = new Database();
        }
        return self::$_database;
    }

    public function getAll($where = '')
    {
        $conn = (self::getDatabase())::getConn();
        $stmt = $conn->prepare('SELECT * FROM ' . $this->_table . ' ' . $where);
        $stmt->execute();
        $rows = (object) $stmt->fetchAll();
        $obj = [];
        foreach ($rows as $row) {
            $that = $this;
            foreach ($row as $k => $v) {
                $that->$k = $v;
            }
            $obj[] = $that;
        }
        return $obj;
    }

    public static function getFirst($where = '1=1', $bindArr = [])
    {
        $conn = (self::getDatabase())::getConn();
        $class = get_called_class();
        $db = new $class();
        $stmt = $conn->prepare('SELECT * FROM ' . $db->_table . ' WHERE ' . $where . ' LIMIT 1');
        $stmt->execute($bindArr);
        $row = (object) $stmt->fetch();
        foreach ($row as $k => $v) {
            $db->$k = $v;
        }
        return $db;
    }

    public static function getFirstId($id)
    {
        return self::getFirst('id = ?', [$id]);
    }

    public function Save()
    {
        if (is_null($this->id)) {
            return $this->Insert();
        }
        return $this->Update();
    }

    private function Insert()
    {
        $ks = '';
        $vs = '';
        unset($this->id);
        $attr = get_object_vars($this);
        foreach ($attr as $k => $v) {
            if (substr($k,0, 1) !== '_' and is_string($k)) {
                $ks .= $k . ",";
                $vs .= "'" . $v . "',";
            }
        }
        $ks = rtrim($ks, ',');
        $vs = rtrim($vs, ',');
        $query = "INSERT INTO " . $this->_table . " (" . $ks . ") VALUES (" . $vs . ")";
        $conn = (self::getDatabase())::getConn();
        return $conn->query($query);
    }

    private function Update()
    {
        $where = 'id = '.$this->id;
        unset($this->id);
        $query = "UPDATE " . $this->_table . " SET ";

        $attr = get_object_vars($this);
        foreach ($attr as $k => $v) {
            if (substr($k,0, 1) !== '_' and is_string($k)) {
                $query .= $k . "='" . $v . "',";
            }
        }
        $query = rtrim($query, ',') . ' WHERE ' . $where;

        $conn = (self::getDatabase())::getConn();
        $stmt = $conn->prepare($query);
        return $stmt->execute();
    }

    public function __call($name, $arguments)
    {
        switch (substr(0, 3, $name)) {
            case 'get':
                $name = str_replace('get', '', $name);
                $name = lcfirst($name);
                $nameUnder = (string) Inflector::underscore($name);
                if (isset($this->$nameUnder)) {
                    return $this->$nameUnder;
                }
                break;
            case 'set':
                $name = str_replace('set', '', $name);
                $name = lcfirst($name);
                $nameUnder = (string) Inflector::underscore($name);
                if (isset($this->$nameUnder)) {
                    $this->$nameUnder = $arguments[0];
                    return $this;
                }
                break;
        }
        return null;
    }
}