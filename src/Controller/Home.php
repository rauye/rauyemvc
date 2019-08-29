<?php

namespace Z2Admin\Controller;

class Home extends Controller
{
    public function index()
    {
        $this->loadView('home');
    }
}