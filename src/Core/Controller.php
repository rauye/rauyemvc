<?php
namespace RauyeMVC\Core;

use RauyeMVC\Config;

class Controller
{
    private $variables = [];

    public function set($name, $value)
    {
        array_push($this->variables, [$name, $value]);
    }

    private function declareVariables()
    {
        foreach ($this->variables as $variable) {
            ${$variable[0]} = $variable[1];
        }
        ${'canais'} = 'sdfds';
    }

    public function loadView($view = null, $includes = true)
    {
        $c = strtolower(
            explode(
                'Controller',
                explode('\Controller\\', get_called_class())[1]
            )[0]
        );

        is_null($view) and $view = debug_backtrace()[1]['function'];
        ($view == 'index') and $view = $c;

        $viewFilename = 'src/View/'.$c.'/'.$view.'.php';
        if (!file_exists($viewFilename)) {
            exit('<h1>View não encontrada.</h1>');
        }

        // Declare variables
        foreach ($this->variables as $variable) {
            ${$variable[0]} = $variable[1];
        }
        $this->variables = [];

        if ($includes) {
            require_once 'src/View/_templates/header.php';
        }

        require_once $viewFilename;
        if ($includes) {
            require_once 'src/View/_templates/footer.php';
        }
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
        die;
    }

    public static function loadViewError(int $error = 500, $exception = null)
    {
        $debugContent = '';
        if (Config::$DEBUG and !is_null($exception)) {
            /** @var \Exception $exception */
            ob_start();
            print_r($exception->getMessage());
            print_r($exception->getTraceAsString());
//            debug_print_backtrace();
            $debugContent = '<pre>' . ob_get_clean() . '</pre>';
        }
        require_once "src/View/_templates/errors/{$error}.php";
        http_response_code($error);
        die;
    }
}