# Statamic Agency

> Statamic Agency provides healthcheck information on your Statamic sites and synchronises your users across your sites.


## How to Install

Run the following command from your project root:

``` bash
composer require thoughtco/statamic-agency
```

Add your Agency account token to the .env key `STATAMIC_AGENCY_ACCOUNT_TOKEN`

e.g. 

```bash
STATAMIC_AGENCY_ACCOUNT_TOKEN="my-token"
```

You can also optionally publish the config: 

```bash
php artisan vendor:publish --tag=statamic-agency
```

## How it works
Once installed Agency will detect and create your site on the platform, assigning a unique `Installation ID` for the site.

Every 4 hours Agency will update the current environment, including your Statamic version, your addon versions, and other metrics that will be useful for you to monitor.


## Hooks
If you want to return or modify the data sent to Agency, you can hook into the update environment payload:

```php
\Thoughtco\StatamicAgency\Facades\Agency::hook('update-environment-payload', function ($payload, $next) {
    // do something to payload
    return $next($payload);
);
```

While we have provided you with the ability to modify any of the payload, we strongly advise you do not change any of the data Agency collects, as it may cause errors in the system.
