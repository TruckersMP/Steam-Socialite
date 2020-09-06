<p align="center"><img src="https://truckersmp.com/assets/img/truckersmp-logo-sm.png"></p>

<p align="center">
<img src="https://github.com/truckersmp/steam-socialite/workflows/Run%20Tests/badge.svg" alt="GitHub Workflow Status">
<a href="https://github.styleci.io/repos/245795520"><img src="https://github.styleci.io/repos/245795520/shield?branch=master" alt="StyleCI Status"></a>
<a href="https://packagist.org/packages/truckersmp/steam-socialite"><img src="https://poser.pugx.org/truckersmp/steam-socialite/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/truckersmp/steam-socialite"><img src="https://poser.pugx.org/truckersmp/steam-socialite/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/truckersmp/steam-socialite"><img src="https://poser.pugx.org/truckersmp/steam-socialite/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/truckersmp/steam-socialite"><img src="https://poser.pugx.org/truckersmp/steam-socialite/license" alt="License"></a>
</p>

## Introduction

The TruckersMP PHP library for [Laravel Socialite][laravel-socialite] provides
an adapter for the Steam OpenID authentication.

## Requirements

- PHP 7.3
- Composer
- [Laravel 6.x][laravel-framework] (or newer)
- [Laravel Socialite 4.4][laravel-socialite] (or newer)

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

As OpenID does not use the `client_id` and `client_secret` properties, the latter
is actually utilized as the Steam API key and is used for fetching the user data.
For more information about the API, check the [Steam developer portal][steam-dev].

Do not forget to put new environment variables into your `.env` application file:

```dotenv
# Steam OpenID
STEAM_SECRET=
STEAM_REDIRECT_URI=
```

**Laravel 5.5+** uses Package Auto-Discovery, so does not require you to manually
add the `SteamSocialiteProvider`.

If you do not use auto-discovery, you will need register the provider in your
`config/app.php` configuration file as a package service provider:

```php
'providers' => [

    // ...

    /*
     * Package Service Providers...
     */
    TruckersMP\SteamSocialite\SteamSocialiteProvider::class,

    // ...

],
```

Consult the [documentation of Laravel Socialite][laravel-socialite-doc] to
implement the application functionality.

## Support

If you have any questions about the library, you can create a topic on our
[forum][developer-forum].

## License

This package is open-source and is licensed under the [MIT license](LICENSE.md).


[laravel-socialite]: https://github.com/laravel/socialite
[laravel-framework]: https://github.com/laravel/framework
[laravel-socialite-doc]: https://laravel.com/docs/master/socialite
[steam-dev]: https://steamcommunity.com/dev/
[developer-forum]: https://forum.truckersmp.com/index.php?/forum/198-developer-portal/
