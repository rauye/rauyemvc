<?php

namespace Z2Admin\Model\Auxiliar;

use Z2Admin\Core\Session;
use Z2Admin\Model\Usuario;

class Auth
{
    private static $userId;
    public static $user;
    public static $pass;
    private static $token;

    public static function login()
    {
        $user = Usuario::getFirst('usuario = ? and senha = ?', [self::$user, self::$pass]);
        if ($user) {
            self::$userId = $user->id;
            self::$token = $user->login_token;
            self::setDataSession();
            return true;
        }
        return false;
    }

    public static function check()
    {
        return self::checkDataSession();
    }

    public static function getUser()
    {
        if (is_null(self::$userId)) {
            self::login();
        }
        return self::$userId;
    }

    public static function logout()
    {
       Session::destroy();
    }

    private static function checkDataSession()
    {
        $userModel = Usuario::getFirst('usuario = ?', [self::$user]);
        $sessionAccess = sha1(md5(self::$user . '-' . $userModel->login_token));
        $access = Session::get('access') == $sessionAccess;
        return $access;
    }

    private static function setDataSession()
    {
        $access = sha1(md5(self::$user . '-' . self::$token));
        Session::set('access', $access);
    }
}