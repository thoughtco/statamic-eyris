<?php

namespace Thoughtco\Eyris\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Facades\Addon;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Statamic;
use Statamic\Support\Traits\Hookable;

class Agent
{
    use Hookable;

    public $client;

    public bool $supportsAddonSettings = false;

    public function __construct()
    {
        $this->supportsAddonSettings = app()->environment('testing') ? true : substr(Statamic::version(), 0, 1) >= 6;
    }

    private function client()
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = Http::withToken(config('statamic-eyris.account_token'))
            ->withHeader('Accept', 'application/json')
            ->baseUrl('https://statamic-agency-app-tuue2udz.ams1.preview.ploi.it/api/');

        return $this->client;
    }

    public function negotiateToken()
    {
        if (! config('statamic-eyris.account_token')) {
            return;
        }

        // hit remote API with the agency token, app URL and IP (?) in exchange for an installation_id
        // to authenticate incoming requests with
        // need some way of recovering the same token in case of the data being cleared
        $response = $this->client()->post('negotiate', [
            'url' => config('app.url'),
            'ip' => request()->server('SERVER_ADDR') ?? request()->server('LOCAL_ADDR'),
        ]);

        if (! $response->successful()) {
            Log::error('Failed to negotiate token with eyris server', ['response' => $response->body()]);

            return;
        }

        if (! $installationId = Arr::get($response->json(), 'installation_id')) {
            Log::error('Failed to negotiate token with eyris server', $response->json());

            return;
        }

        return $installationId;
    }

    public function saveSettings(Collection $settings): void
    {
        if ($this->supportsAddonSettings) {
            Addon::get('thoughtco/statamic-eyris')->settings()->set($settings->all())->save();

            return;
        }

        File::put(resource_path('addons/statamic-eyris.yaml'), json_encode($settings->all()));
    }

    public function settings(): Collection
    {
        if ($this->supportsAddonSettings) {
            return collect(Addon::get('thoughtco/statamic-eyris')->settings()->all());
        }

        $path = resource_path('addons/statamic-eyris.yaml');

        if (! File::exists($path) || ! $file = File::get($path)) {
            return collect();
        }

        if (! $json = YAML::parse($file)) {
            return collect();
        }

        return collect($json);
    }

    public function getAnnouncements()
    {
        $settings = $this->settings();

        if (! $installationId = $settings->get('installation_id')) {
            return;
        }

        return $this->client->post('announcements', ['installation_id' => $installationId])->json();
    }

    public function updateEnvironment()
    {
        $settings = $this->settings();

        if (! $installationId = $settings->get('installation_id')) {
            return;
        }

        if ($settings->get('last_environment_update', 0) > now()->subMinutes(60)->timestamp) {
            return;
        }

        $opcacheEnabled = false;
        try {
            $opcacheEnabled = opcache_get_status();
            $opcacheEnabled = $opcacheEnabled['opcache_enabled'];
        } catch (\Throwable $e) {
        }

        $payload = [
            'installation_id' => $installationId,
            'laravel' => [
                'cache' => config('cache.default'),
                'config_cached' => app()->configurationIsCached(),
                'debug' => config('app.debug'),
                'environment' => app()->environment(),
                'events_cached' => app()->eventsAreCached(),
                'queue' => config('queue.default'),
                'name' => config('app.name'),
                'routes_cached' => app()->routesAreCached(),
                'url' => config('app.url'),
                'version' => app()->version(),
                'views_cached' => $this->viewsAreCached(),
            ],
            'php' => [
                'opcache_enabled' => $opcacheEnabled,
                'os' => PHP_OS,
                'version' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
            ],
            'statamic' => [
                'addons' => Addon::all()
                    ->map(function ($addon) {
                        // avoid hitting statamic during tests
                        if (app()->environment('testing')) {
                            return [];
                        }

                        return [
                            'name' => $addon->name(),
                            'marketplace_url' => $addon->marketplaceUrl(),
                            'package' => $addon->package(),
                            'version' => $addon->version(),
                        ];
                    })->all(),
                'debugbar_enabled' => debugbar()->enabled(),
                'pro' => Statamic::pro(),
                'static_caching' => config('statamic.static_caching.strategy'),
                'watcher_enabled' => Stache::isWatcherEnabled(),
                'version' => app()->environment('testing') ? '6.0.0' : Statamic::version(),
            ],
            'other' => $this->runHooks('update-environment-payload', []),
            'packages' => [],
        ];

        $lockPath = base_path('composer.lock');
        if (File::exists($lockPath) && ($lock = File::json($lockPath))) {
            $lock = collect($lock['packages'] ?? []);

            $payload['packages'] = collect($lock)
                ->map(function ($package) {
                    return [
                        'name' => $package['name'],
                        'version' => $package['version'],
                        'is_public' => Str::of(Arr::get($package, 'dist.url', ''))->contains('://'),
                    ];
                })
                ->filter()
                ->values()
                ->sortBy('name')
                ->all();
        }

        $this->client()->post('environment', $payload);

        $settings->put('last_environment_update', now()->timestamp);
        $this->saveSettings($settings);
    }

    private function viewsAreCached()
    {
        return count(glob(config('view.compiled').'/*.php')) > 0;
    }
}
