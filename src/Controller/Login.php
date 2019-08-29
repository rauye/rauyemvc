<?php

namespace Z2Admin\Controller;

class Login extends Controller
{
    public function index()
    {
        $this->loadView('index', false);
    }
}