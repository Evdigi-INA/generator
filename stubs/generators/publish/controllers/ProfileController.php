<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    public function __invoke(): View
    {
        return view(view: 'profile');
    }
}
