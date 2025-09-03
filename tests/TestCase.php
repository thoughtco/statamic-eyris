<?php

namespace Thoughtco\StatamicAgency\Tests;

use Illuminate\Encryption\Encrypter;
use Statamic\Testing\AddonTestCase;
use Thoughtco\StatamicAgency\ServiceProvider;

class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected $shouldFakeVersion = true;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(Encrypter::generateKey($app['config']['app.cipher'])));

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);
    }
}
