<?php

namespace Numa\Aodao;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Yhcc extends LaravelFacade
{
    /**
     * 默认为 Server.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'yhcc';
    }
}
