<?php

namespace Stereoide\Github;

use Illuminate\Support\Facades\Facade;

class GithubFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'github';
    }
}