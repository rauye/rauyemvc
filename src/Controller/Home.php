<?php

namespace RauyeMVC\Controller;

use RauyeMVC\Core\Controller;

class Home extends Controller
{
    public function index()
    {
        $this->loadView('home');
    }
}