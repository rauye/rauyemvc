<?php

namespace RauyeMVC\Support;

class Request
{
    private $data;

    public function __construct()
    {
        $this->data = (object) $_REQUEST;
    }

    public function input($name, $default = null)
    {
        if (!isset($this->data->$name)) {
            if (!is_null($default)) {
                return $default;
            }
            return null;
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

    public static function createFromGlobals()
    {
        $request = new Request();
        $request->data = (object) $_REQUEST;
        return $request;
    }
}