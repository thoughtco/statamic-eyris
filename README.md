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
If you want to return extra data to Agency, you can hook into the update environment payload:

```php
\Thoughtco\StatamicAgency\Facades\Agency::hook('update-environment-payload', function ($payload, $next) {
    // add to payload, this should be in the following format
    // eg [ ['label' => 'My label', 'value' => 'my_value'] ]
    return $next($payload);
);
```
