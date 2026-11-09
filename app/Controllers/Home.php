<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        //helper('url');

        return view('login.php');
    }

    public function singUp(): string
    {
        //helper('url');

        return view('sing_up.php');
    }
}
