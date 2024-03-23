<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Breadcrumb extends Component
{

    public function __construct(public $breadcrumbs)
    {
        //
    }

    public function render()
    {
        return view('components.breadcrumb');
    }
}
