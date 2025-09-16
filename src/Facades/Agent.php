<?php

namespace Thoughtco\Eyris\Facades;

use Illuminate\Support\Facades\Facade;
use Thoughtco\Eyris\Managers\Agent as Manager;

class Agent extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
