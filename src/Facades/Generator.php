<?php

namespace EvdigiIna\Generator\Facades;

use Illuminate\Support\Facades\Facade;

class Generator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'generator';
    }
}
