<?php
namespace Z2Admin\Controller;

class Controller
{
    public function index()
    {
        echo 'Esse é o controller padrão';
    }

    public function loadView($view = null, $includes = true)
    {
        $c = strtolower(explode('\Controller\\', get_called_class())[1]);

        is_null($view) and $view = debug_backtrace()[1]['function'];
        ($view == 'index') and $view = $c;

        $viewFilename = 'src/View/'.$c.'/'.$view.'.php';
        if (!file_exists($viewFilename)) {
            exit('<h1>View não encontrada.</h1>');
        }

        if ($includes) {
            require_once 'src/View/_templates/header.php';
        }
        require_once $viewFilename;
        if ($includes) {
            require_once 'src/View/_templates/footer.php';
        }
    }
}