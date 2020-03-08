<p align="center"><img src="https://truckersmp.com/assets/img/truckersmp-logo-sm.png"></p>

## Introduction

The TruckersMP PHP library for [Laravel Socialite][laravel-socialite] provides
an adapter for the Steam OpenID authentication.

## Requirements

- PHP 7.3
- Composer
- [Laravel 6.x][laravel-framework] (or newer)
- [Laravel Socialite 4.2][laravel-socialite] (or newer)

## Installation

To get started, use Composer to add the package to your application:

```shell script
composer require truckersmp/steam-socialite
```

Following the [documentation of Laravel Socialite][laravel-socialite-doc],
you will also need to add credentials for the Steam service. These credentials
should be placed in your `config/services.php` configuration file:

```php
'steam' => [
    'client_id' => null,
    'client_secret' => env('STEAM_SECRET'),
    'redirect' => env('STEAM_REDIRECT_URI'),
],
```

Do not forget to put new environment variables into your `.env` application file:

```dotenv
# Steam OpenID
STEAM_SECRET=
STEAM_REDIRECT_URI=
```

Consult the [documentation of Laravel Socialite][laravel-socialite-doc] to
implement the application functionality.

## Support

If you have any questions about the library, you can create a topic on our
[forum](https://forum.truckersmp.com/index.php?/forum/198-developer-portal/).

## License

This package is open-source and is licensed under the [MIT license](LICENSE.md).


[laravel-socialite]: https://github.com/laravel/socialite
[laravel-framework]: https://github.com/laravel/framework
[laravel-socialite-doc]: https://laravel.com/docs/master/socialite
