<?php

namespace Thoughtco\Eyris\Http\Middleware;

use Closure;
use Thoughtco\Eyris\Facades\Agent;

class VerifyEyrisHeaders
{
    public function handle($request, Closure $next)
    {
        if (! config('statamic-eyris.enabled')) {
            return $this->invalidResponse();
        }

        if (! $token = config('statamic-eyris.account_token')) {
            return $this->invalidResponse();
        }

        if ($token != $request->bearerToken()) {
            return $this->invalidResponse();
        }

        if (! $addonSettings = Agent::settings()) {
            return $this->invalidResponse();
        }

        if ($addonSettings->get('installation_id') != $request->header('X-Eyris-Installation-Id')) {
            return $this->invalidResponse();
        }

        return $next($request);
    }

    private function invalidResponse()
    {
        return response('Unauthorized', 401);
    }
}
