<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProfilController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('profil', [
            'username'   => session()->get('username'),
            'email'      => session()->get('email'),
            'role'       => session()->get('role'),
            'loginTime'  => session()->get('loginTime'),
            'status'     => session()->get('isLoggedIn') ? 'Aktif' : 'Tidak Aktif',
        ]);
    }
}