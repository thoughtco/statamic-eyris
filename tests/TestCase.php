<?php

namespace Thoughtco\StatamicAgency\Tests;

use Illuminate\Encryption\Encrypter;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Thoughtco\StatamicAgency\ServiceProvider;

class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected $shouldFakeVersion = true;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(Encrypter::generateKey($app['config']['app.cipher'])));

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists($this->fakeStacheDirectory)) {
            mkdir($this->fakeStacheDirectory, 0777, true);
        }
    }
}
