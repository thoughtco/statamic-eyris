<?php

namespace Thoughtco\StatamicAgency\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Addon;
use Statamic\Facades\User;
use Thoughtco\StatamicAgency\Tests\TestCase;

class LoginControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic-agency.account_token', 'some-token');

        $settings = Addon::get('thoughtco/statamic-agency')->settings();
        $settings->set('installation_id', 1);
        $settings->save();
    }

    #[Test]
    public function doesnt_generate_a_link_when_there_is_no_bearer_token()
    {
        $response = $this
            ->withHeader('X-Agency-Installation-Id', 1)
            ->postJson(route('statamic-agency.generate-login'), []);

        $this->assertSame('Unauthorized', $response->getContent());
    }

    #[Test]
    public function doesnt_generate_a_link_when_the_bearer_token_mismatches()
    {
        $response = $this
            ->withToken('not-some-token')
            ->withHeader('X-Agency-Installation-Id', 1)
            ->postJson(route('statamic-agency.generate-login'), []);

        $this->assertSame('Unauthorized', $response->getContent());
    }

    #[Test]
    public function doesnt_generate_a_link_when_there_in_no_installation_id()
    {
        $response = $this
            ->withToken('not-some-token')
            ->postJson(route('statamic-agency.generate-login'), []);

        $this->assertSame('Unauthorized', $response->getContent());
    }

    #[Test]
    public function doesnt_generate_a_link_when_the_installation_id_mismatches()
    {
        $response = $this
            ->withToken('not-some-token')
            ->withHeader('X-Agency-Installation-Id', 2)
            ->postJson(route('statamic-agency.generate-login'), []);

        $this->assertSame('Unauthorized', $response->getContent());
    }

    #[Test]
    public function doesnt_generate_a_link_when_there_is_no_email()
    {
        $response = $this
            ->withToken('some-token')
            ->withHeader('X-Agency-Installation-Id', 1)
            ->postJson(route('statamic-agency.generate-login'), []);

        $this->assertJsonStringEqualsJsonString('{"error":"invalid_email"}', $response->getContent());
    }

    #[Test]
    public function generates_a_link_for_a_new_user()
    {
        User::all()->each->delete();
        $this->assertSame(0, User::count());

        $response = $this
            ->withToken('some-token')
            ->withHeader('X-Agency-Installation-Id', 1)
            ->postJson(route('statamic-agency.generate-login'), [
                'email' => 'test@test.com',
            ]);

        $json = $response->json();

        $this->assertArrayHasKey('url', $json);
        $this->assertStringContainsString('!/statamic-agency/login/', $json['url']);

        $id = Str::of($json['url'])->before('?')->afterLast('/');

        $this->assertSame(1, User::count());
        $user = User::all()->first();
        $this->assertTrue($user->isSuper());
        $this->assertSame(Cache::get('statamic-agency::'.$id), $user->id());
    }

    #[Test]
    public function generates_a_link_for_an_existing_user()
    {
        User::all()->each->delete();
        $user = tap(User::make()->email('test@test.com'))->save();
        $this->assertSame(1, User::count());

        $response = $this
            ->withToken('some-token')
            ->withHeader('X-Agency-Installation-Id', 1)
            ->postJson(route('statamic-agency.generate-login'), [
                'email' => 'test@test.com',
            ]);

        $json = $response->json();

        $this->assertArrayHasKey('url', $json);
        $this->assertStringContainsString('!/statamic-agency/login/', $json['url']);

        $id = Str::of($json['url'])->before('?')->afterLast('/');

        $this->assertSame(Cache::get('statamic-agency::'.$id), $user->id());
    }
}
