<?php 

namespace App\Controllers;

class Init extends BaseController
{
    public function login(): string
    {
        return view('login.php', [
            'loginError' => session()->getFlashdata('loginError'),
            'oldEmail'   => session()->getFlashdata('oldEmail') ?? '',
        ]);
    }

    public function singUp(): string
    {
        return view('sing_up.php', [
            'success' => session()->getFlashdata('success'),
        ]);
    }
}