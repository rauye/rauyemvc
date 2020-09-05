<?php

use \RauyeMVC\Config;
use \Illuminate\Http\Request;

header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';

if (Config::$DEBUG) {
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}

function toCamelCase($string) {
    $string = str_replace('-', ' ', $string);
    return str_replace(' ', '', lcfirst(ucwords($string)));
}

function showError($e)
{
    echo '<h1>RauyeMVC</h1>';
    echo '<h2 style="color: red;">Erro interno 500</h2>';
    echo $e->getMessage() . "\n\n";
    echo '<pre><div style="border: 1px solid #ccc; padding: 10px;width: 98%;white-space: break-spaces;">';
    debug_print_backtrace();
    echo '</div>';
    http_response_code(500);die;
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
        exit('<h1>RauyeMVC</h1><h2>Controller não encontrado.</h2>'.$e->getMessage());
    }
    showError($e);
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
    if (strpos($e->getMessage(), 'Call to undefined method ' . ltrim($controller, "\\")) !== false) {
        exit('<h1>RauyeMVC</h1><h2>Action não encontrada.</h2>'.$e->getMessage());
    }
    showError($e);
}