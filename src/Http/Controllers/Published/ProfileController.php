<?php

namespace App\Http\Controllers;

class ProfileController extends Controller
{
    public function __invoke(): \Illuminate\Contracts\View\View
    {
        return view('profile');
    }
}
