<?php

namespace RauyeMVC\Core;

class Request
{
    private $data;

    public function __construct()
    {
        $this->data = (object) $_REQUEST;
    }

    public function input($name, $default)
    {
        if (is_null($this->data->$name) and !is_null($default)) {
            return $default;
        }
        return $this->data->$name;
    }

    public function exists($name)
    {
        return isset($this->data->$name);
    }

    public function all()
    {
        return $this->data;
    }
}