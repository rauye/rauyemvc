<?php

namespace RauyeMVC\Core;

use Cake\Utility\Inflector;
use RauyeMVC\Support\Database;

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

    public static function getAll($where = '')
    {
        $self = new static();
        $conn = ($self::getDatabase())::getConn();
        $stmt = $conn->prepare('SELECT * FROM ' . $self->_table . ' ' . $where);
        $stmt->execute();
        $rows = (object) $stmt->fetchAll();
        $obj = [];
        foreach ($rows as $row) {
            $that = clone $self;
            foreach ($row as $k => $v) {
                $that->$k = $v;
            }
            $obj[] = $that;
        }
        return $obj;
    }

    /**
     * @param string $where
     * @param array|string $bindArr
     * @return $this
     */
    public static function getFirst($where = '1=1', $bindArr = [])
    {
        $conn = (self::getDatabase())::getConn();
        $class = get_called_class();
        $db = new $class();
        $stmt = $conn->prepare('SELECT * FROM ' . $db->_table . ' WHERE ' . $where . ' LIMIT 1');
        if (is_string($bindArr)) {
            $stmt->execute([$bindArr]);
        } else {
            $stmt->execute($bindArr);
        }
        $row = (object) $stmt->fetch();
        if (is_null($row) or isset($row->scalar)) {
            return null;
        }
        foreach ($row as $k => $v) {
            $db->$k = $v;
        }
        return $db;
    }

    /**
     * @param $id
     * @return $this
     */
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

    public function Insert()
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
        $result = $conn->query($query);

        $result and $this->id = $conn->lastInsertId();
        return $this;
    }

    public function Update()
    {
        $id = $this->id;
        unset($this->id);
        $where = 'id = '.$id;
        $query = "UPDATE " . $this->_table . " SET ";

        $attr = get_object_vars($this);
        foreach ($attr as $k => $v) {
            if (substr($k,0, 1) !== '_' and is_string($k)) {
                if (is_null($v)) continue;
                $query .= $k . "='" . $v . "',";
            }
        }
        $query = rtrim($query, ',') . ' WHERE ' . $where;

        $conn = (self::getDatabase())::getConn();
        $stmt = $conn->prepare($query);

        $this->id = $id;

        try {
            $stmt->execute();
        } catch (\Exception $ex) {
            var_dump('Erro executando a query: ' . $query);
            throw $ex;
        }
    }

    public function Delete()
    {
        $where = 'id = '.$this->id;
        unset($this->id);
        $query = "DELETE FROM " . $this->_table . " WHERE " . $where;
        $conn = (self::getDatabase())::getConn();
        $stmt = $conn->query($query);
        return $this;
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