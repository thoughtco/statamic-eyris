<?php

namespace Thoughtco\StatamicAgency\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Addon;
use Statamic\Facades\Stache;
use Statamic\Statamic;
use Statamic\Support\Traits\Hookable;

class Agency
{
    use Hookable;

    public $client;

    public function __construct()
    {
        $this->client = Http::withToken(config('statamic-agency.account_token'))
            ->withHeader('Accept', 'application/json')
            ->baseUrl('https://statamic-agency-app.test/api/');
    }

    public function negotiateToken()
    {
        if (! config('statamic-agency.account_token')) {
            return;
        }

        // hit remote API with the agency token, app URL and IP (?) in exchange for an installation_id
        // to authenticate incoming requests with
        // need some way of recovering the same token in case of the data being cleared
        $response = $this->client->post('negotiate', [
            'url' => config('app.url'),
            'ip' => request()->server('SERVER_ADDR') ?? request()->server('LOCAL_ADDR'),
        ]);

        if (! $response->successful()) {
            Log::error('Failed to negotiate token with agency server', ['response' => $response->body()]);

            return;
        }

        if (! $installationId = Arr::get($response->json(), 'installation_id')) {
            Log::error('Failed to negotiate token with agency server', $response->json());

            return;
        }

        return $installationId;
    }

    public function updateEnvironment()
    {
        $settings = Addon::get('thoughtco/statamic-agency')->settings();

        if (! $installationId = $settings->get('installation_id')) {
            return;
        }

        if ($settings->get('last_environment_update', 0) > now()->subMinutes(60)->timestamp) {
            return;
        }

        $payload = [
            'installation_id' => $installationId,
            'laravel' => [
                'cache' => config('cache.default'),
                'debug' => config('app.debug'),
                'environment' => app()->environment(),
                'queue' => config('queue.default'),
                'name' => config('app.name'),
                'url' => config('app.url'),
                'version' => app()->version(),
            ],
            'php' => [
                'os' => PHP_OS,
                'version' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
            ],
            'statamic' => [
                'addons' => Addon::all()
                    ->map(function ($addon) {
                        return [
                            'name' => $addon->name(),
                            'latest' => $addon->changelog()?->latest()->version,
                            'marketplace_url' => $addon->marketplaceUrl(),
                            'package' => $addon->package(),
                            'version' => $addon->version(),
                        ];
                    })->all(),
                'pro' => Statamic::pro(),
                'watcher_enabled' => Stache::isWatcherEnabled(),
                'version' => Statamic::version(),
            ],
        ];

        // do we check for other common packages like livewire/inertia?

        // @TODO: do we only let them add to a meta array?
        $this->client->post('environment', $this->runHooks('update-environment-payload', $payload));

        $settings->set('last_environment_update', now()->timestamp);
        $settings->save();
    }
}
