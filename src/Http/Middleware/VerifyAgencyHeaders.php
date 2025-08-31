<?php

namespace Thoughtco\StatamicAgency\Http\Middleware;

use Closure;
use Thoughtco\StatamicAgency\Facades\Agency;

class VerifyAgencyHeaders
{
    public function handle($request, Closure $next)
    {
        if (! config('statamic-agency.enabled')) {
            return $this->invalidResponse();
        }

        if (! $token = config('statamic-agency.account_token')) {
            return $this->invalidResponse();
        }

        if ($token != $request->bearerToken()) {
            return $this->invalidResponse();
        }

        if (! $addonSettings = Agency::settings()) {
            return $this->invalidResponse();
        }

        if ($addonSettings->get('installation_id') != $request->header('X-Agency-Installation-Id')) {
            return $this->invalidResponse();
        }

        return $next($request);
    }

    private function invalidResponse()
    {
        return response('Unauthorized', 401);
    }
}
