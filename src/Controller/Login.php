<?php

namespace RauyeMVC\Controller;

use RauyeMVC\Core\Controller;

class Login extends Controller
{
    public function index()
    {
        $this->loadView('index', false);
    }
}