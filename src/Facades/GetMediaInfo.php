<?php

namespace Owenoj\LaravelGetId3\Facades;

use Illuminate\Support\Facades\Facade;

class GetMediaInfo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'GetId3';
    }
}
