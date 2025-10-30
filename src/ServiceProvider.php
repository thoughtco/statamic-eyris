<?php

namespace Thoughtco\Eyris;

use Illuminate\Console\Scheduling\Schedule;
use Statamic\Providers\AddonServiceProvider;
use Thoughtco\Eyris\Facades\Agent;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'publicDirectory' => 'dist',
        'hotFile' => 'vendor/statamic-eyris/hot',
        'input' => [
            'resources/js/cp.js',
        ],
    ];

    public function boot()
    {
        parent::boot();

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-eyris');

        $this->mergeConfigFrom($config = __DIR__.'/../config/statamic-eyris.php', 'statamic-eyris');

        $this->publishes([
            $config => config_path('statamic-eyris.php'),
        ], 'statamic-eyris-config');
    }

    public function booted(\Closure $callback)
    {
        parent::booted($callback);

        $schedule = $this->app->make(Schedule::class);
        $schedule->command('eyris:update-environment')->hourly();
    }

    public function bootAddon()
    {
        $this->setupIfRequired();
    }

    private function setupIfRequired()
    {
        $addonSettings = Agent::settings();

        if ($addonSettings->get('installation_id')) {

            if ($addonSettings->get('last_environment_update', 0) < now()->subMinutes(60)->timestamp) {
                dispatch(fn () => Agent::updateEnvironment())->afterResponse();
            }

            return;
        }

        if ($token = Agent::negotiateToken()) {
            $addonSettings->put('installation_id', $token);
            Agent::saveSettings($addonSettings);

            Agent::updateEnvironment();
        }
    }
}
