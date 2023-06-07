<?php

namespace RauyeMVC\Core;

use RauyeMVC\Support\Database;
use RauyeMVC\Support\Inflector;

class Model
{
    protected static $_idField = 'id';
    protected $_table = null;
    protected static $_database;
    protected $_dbname;

    public function __construct()
    {
        self::$_database = new Database();
        $this->setTableName();
        $this->{static::$_idField} = null;
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
        if (is_null(static::$_database)) {
            static::$_database = new Database();
        }
        return static::$_database;
    }

    public static function getAll($where = '')
    {
        $class = get_called_class();
        $db = new $class();
        $conn = ($db::getDatabase())::getConn();
        empty($where) && $where = '1';
        $stmt = $conn->prepare('SELECT * FROM ' . (($db->_dbname . '.') ?: '') . $db->_table . ' WHERE ' . $where);
        $stmt->execute();
        $rows = (object) $stmt->fetchAll();
        $obj = [];
        foreach ($rows as $row) {
            $that = clone $db;
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
        $stmt = $conn->prepare('SELECT * FROM ' . (($db->_dbname . '.') ?: '') . $db->_table . ' WHERE ' . $where . ' LIMIT 1');
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
        return self::getFirst(static::$_idField . ' = ?', [$id]);
    }

    public function Save()
    {
        if (is_null(static::$_idField)) {
            return $this->Insert();
        }
        return $this->Update();
    }

    public function Insert()
    {
        $ks = '';
        $vs = '';
        unset($this->{static::$_idField});
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

        $result and $this->{static::$_idField} = $conn->lastInsertId();
        return $this;
    }

    public function Update()
    {
        $id = $this->{static::$_idField};
        unset($this->{static::$_idField});
        $where = $this->{static::$_idField} . ' = '.$id;
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

        $this->{static::$_idField} = $id;

        try {
            $stmt->execute();
        } catch (\Exception $ex) {
            var_dump('Erro executando a query: ' . $query);
            throw $ex;
        }
    }

    public function Delete()
    {
        $where = $this->{static::$_idField} . ' = ' . $this->{static::$_idField};
        unset($this->{static::$_idField});
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