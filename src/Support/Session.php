<?php

namespace RauyeMVC\Support;

class Session
{
    public function __construct()
    {
        self::start();
    }

    private static function checkStarted()
    {
        if (session_status() == PHP_SESSION_NONE) {
            return false;
        }
        return true;
    }

    private static function start()
    {
        self::checkStarted() or session_start();
    }

    public static function set($name, $value)
    {
        self::start();
        $_SESSION[$name] = $value;
        return true;
    }

    public static function get($name)
    {
        self::start();
        return $_SESSION[$name] ?? null;
    }

    public static function destroy()
    {
        self::checkStarted() and session_destroy();
        return true;
    }
}