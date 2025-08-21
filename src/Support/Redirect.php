<?php

namespace RauyeMVC\Support;

class Redirect
{
    private static $url;

    public static function setUrl(string $url)
    {
        self::$url = $url;
        return new static();
    }

    public static function setController(string $controller)
    {
        $controller = explode("\Controller\\", $controller)[1];
        $controller = explode('Controller', $controller)[0];
        self::$url = sprintf('/%s/', Inflector::underscore($controller));
        return new static();
    }

    public static function setAction(string $action)
    {
        self::$url .= $action;
        return new static();
    }

    public static function go()
    {
        header("Location: " . self::$url);
        die;
    }
}