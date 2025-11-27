# Statamic Eyris

> Statamic Eyris provides healthcheck information on your Statamic sites and synchronises your users across your sites.


## How to Install

Run the following command from your project root:

``` bash
composer require thoughtco/statamic-eyris
```

Sign up for an account at [www.eyris.app](https://www.eyris.app), then add your Eyris account token to your .env file:

```bash
EYRIS_TOKEN="my-token"
```

You can also optionally publish the config: 

```bash
php artisan vendor:publish --tag=statamic-eyris
```

## Documentation

Documentation for this addon is available at [https://docs.eyris.app](https://docs.eyris.app).
