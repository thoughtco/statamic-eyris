<?php

namespace Thoughtco\StatamicAgency;

use Illuminate\Console\Scheduling\Schedule;
use Statamic\Facades\Addon;
use Statamic\Providers\AddonServiceProvider;
use Thoughtco\StatamicAgency\Facades\Agency;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom($config = __DIR__.'/../config/statamic-agency.php', 'statamic-agency');

        $this->publishes([
            $config => config_path('statamic-agency.php'),
        ], 'statamic-agency-config');
    }

    public function booted(\Closure $callback)
    {
        parent::booted($callback);

        $schedule = $this->app->make(Schedule::class);
        $schedule->command('agency:update-environment')->everyFourHours();
    }

    public function bootAddon()
    {
        $this->setupIfRequired();
    }

    private function setupIfRequired()
    {
        $addonSettings = Addon::get('thoughtco/statamic-agency')->settings();

        if ($addonSettings->get('installation_id')) {

            if ($addonSettings->get('last_environment_update', 0) < now()->subMinutes(60)->timestamp) {
                Agency::updateEnvironment();
            }

            return;
        }

        if ($token = Agency::negotiateToken()) {
            $addonSettings->set('installation_id', $token);
            $addonSettings->save();

            Agency::updateEnvironment();
        }
    }
}
