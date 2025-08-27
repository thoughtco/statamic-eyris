<?php

namespace Thoughtco\StatamicCacheTracker\Facades;

use Illuminate\Support\Facades\Facade;
use Thoughtco\StatamicAgency\Managers\Agency as Manager;

class Agency extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
