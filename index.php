<?php

use Illuminate\Http\Request;
use RauyeMVC\Config;
use RauyeMVC\Core\Controller;

header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';

ini_set('display_errors', Config::$DEBUG);

function toCamelCase($string)
{
    $string = str_replace('-', ' ', $string);
    return str_replace(' ', '', lcfirst(ucwords($string)));
}

function t(string $string)
{
    if (!is_null(Config::$TRANSLATE_MODEL)) {
        $modelName = Config::$TRANSLATE_MODEL;
        $model = new $modelName();
        /** @var \RauyeMVC\Core\Model $model */
        $model = $model::getFirst('value = ?', $string);
        if (is_null($model)) {
            $langCode = Config::$LANGUAGE_CODE;
            $model = new $modelName();
            $model->value = $string;
            $model->$langCode = $string;
            $model->Save();
        }
        $lang = Config::$LANGUAGE;
        return $model->$lang ?? $model->value;
    }
    return $string;
}

$page = toCamelCase($_GET['page'] ?? '');
$action = toCamelCase($_GET['action'] ?? 'index');
$param = $_GET['param'] ?? null;
unset($_GET['page'], $_GET['action'], $_GET['param'], $_REQUEST['page'], $_REQUEST['action'], $_REQUEST['param']);

if (empty($page)) {
    $page = Config::$DEFAULT_CONTROLLER;
    $action = 'index';
}
$controller = '\\' . Config::$PROJECT_VENDOR_NAME . '\Controller\\' . ucfirst($page) . 'Controller';

try {
    $c = new $controller();
} catch (Error $e) {
    if (strpos($e->getMessage(), "Class '$controller' not found") !== false) {
        Controller::loadViewError(404, $e);
    }
}

try {
    $r = new ReflectionMethod($c, $action);
    $params = $r->getParameters();
    if (isset($params[0])) {
        if ($params[0]->name == 'request') {
            $request = Request::createFromGlobals();
            if (isset($params[1])) {
                $c->$action($request, $param);
            } else {
                $c->$action($request);
            }
        } else {
            $c->$action($param);
        }
    } else {
        $c->$action();
    }
} catch (Error $e) {
    $re = '/Call to undefined method (.*)/m';
    preg_match_all($re, $e->getMessage(), $matches, PREG_SET_ORDER, 0);
    if ($matches === false) {
        Controller::loadViewError(400, $e);
    }
    Controller::loadViewError(500, $e);
} catch (Exception $ex) {
    $re = '/Method (.*) does not exist/m';
    preg_match_all($re, $ex->getMessage(), $matches, PREG_SET_ORDER, 0);
    if ($matches === false) {
        Controller::loadViewError(400, $ex);
    }
    Controller::loadViewError(500, $ex);
}