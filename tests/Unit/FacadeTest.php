<?php

namespace Thoughtco\StatamicAgency\Tests\Unit;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Addon;
use Thoughtco\StatamicAgency\Facades\Agency;
use Thoughtco\StatamicAgency\Tests\TestCase;

class FacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $settings = Addon::get('thoughtco/statamic-agency')->settings();
        $settings->set('installation_id', 1);
        $settings->save();
    }

    #[Test]
    public function can_negotiate_a_token()
    {
        Http::fake([
            '*' => Http::response(['installation_id' => 1], 200, ['Content-Type' => 'application/json']),
        ]);

        config()->set('statamic-agency.account_token', 'some-token');

        $response = Agency::negotiateToken();

        Http::assertSent(function ($request) {
            return stripos($request->url(), '/api/negotiate') !== false
                && $request->body() === '{"url":"http:\/\/localhost","ip":null}';
        });

        $this->assertSame(1, $response);
    }

    #[Test]
    public function can_update_an_environment()
    {
        $this->freezeTime();

        Http::fake([
            '*' => Http::response('', 200, ['Content-Type' => 'application/json']),
        ]);

        config()->set('statamic-agency.account_token', 'some-token');

        $settings = Addon::get('thoughtco/statamic-agency')->settings();
        $settings->set('installation_id', 1);
        $settings->set('last_environment_update', 0);
        $settings->save();

        Agency::updateEnvironment();

        Http::assertSent(function ($request) {
            if (stripos($request->url(), '/api/environment') === false) {
                return false;
            }

            $payload = json_decode($request->body(), true);

            return array_key_exists('installation_id', $payload)
                && array_key_exists('laravel', $payload)
                && array_key_exists('php', $payload)
                && array_key_exists('statamic', $payload)
                && array_key_exists('other', $payload)
                && array_key_exists('packages', $payload);
        });

        $this->assertSame(Addon::get('thoughtco/statamic-agency')->settings()->get('last_environment_update'), time().'');
    }

    #[Test]
    public function runs_hooks_on_update_environment()
    {
        $this->freezeTime();

        Http::fake([
            '*' => Http::response('', 200, ['Content-Type' => 'application/json']),
        ]);

        config()->set('statamic-agency.account_token', 'some-token');

        $settings = Addon::get('thoughtco/statamic-agency')->settings();
        $settings->set('installation_id', 1);
        $settings->set('last_environment_update', 0);
        $settings->save();

        Agency::hook('update-environment-payload', function ($payload, $next) {
            $payload['foo'] = 'bar';

            return $next($payload);
        });

        Agency::updateEnvironment();

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);

            return array_key_exists('foo', $payload['other']);
        });
    }
}
