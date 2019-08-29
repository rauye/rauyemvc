<?php
header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';
require 'config.php';

if (__DEBUG__) {
    ini_set('display_errors', 'On');
}

function toCamelCase($string) {
    $string = str_replace('-', ' ', $string);
    return str_replace(' ', '', lcfirst(ucwords($string)));
}

$page = toCamelCase($_GET['page'] ?? '');
$action = toCamelCase($_GET['action'] ?? 'index');
if (empty($page)) {
    $page = __DEFAULT_CONTROLLER__;
    $action = 'index';
}
$controller = '\Z2Admin\Controller\\' . ucfirst($page);

try {
    $c = new $controller();
} catch (Error $e) {
    exit('<h1>Controller não encontrado.</h1>'.$e->getMessage());
}

try {
    $c->$action();
} catch (Error $e) {
    exit('<h1>Action não encontrada.</h1>'.$e->getMessage());
}




//list( $controller, $function, $params ) = explode( '/', $uri, 3 );
//$params = explode( '/', $uri );
//RewriteCond %{REQUEST_FILENAME} !-f
//RewriteCond %{REQUEST_FILENAME} !-d
//RewriteRule ^(.*)$ mvc.php [L,NS]