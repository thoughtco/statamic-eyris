# Statamic Eyris

> Statamic Eyris provides healthcheck information on your Statamic sites and synchronises your users across your sites.


## How to Install

Run the following command from your project root:

``` bash
composer require thoughtco/statamic-eyris
```

Add your Agency account token to the .env key `STATAMIC_EYRIS_ACCOUNT_TOKEN`

e.g. 

```bash
STATAMIC_EYRIS_ACCOUNT_TOKEN="my-token"
```

You can also optionally publish the config: 

```bash
php artisan vendor:publish --tag=statamic-eyris
```

## How it works
Once installed Eyris will detect and create your site on the platform, assigning a unique `Installation ID` for the site.

Once every hour Eyris will update the current environment, including your Statamic version, your addon versions, and other metrics that will be useful for you to monitor. If you have the task scheduler enabled this will happen via the console, otherwise it will happen as part of a visitor's hit to your website.


## Hooks
If you want to return extra data to Eyris, you can hook into the update environment payload:

```php
\Thoughtco\Eyris\Facades\Agent::hook('update-environment-payload', function ($payload, $next) {
    // add to payload, this should be in the following format
    // eg [ ['label' => 'My label', 'value' => 'my_value'] ]
    return $next($payload);
);
```

## Widget
We provide an announcement widget to let you push announcements from the system to your site.

To enable it add `eyris-announcements` to `config/statamic/cp.php` under the 'widgets' key.
