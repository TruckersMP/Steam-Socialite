<?php

namespace TruckersMP\SteamSocialite;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use TruckersMP\SteamSocialite\Providers\SteamProvider;

class SteamSocialiteProvider extends ServiceProvider
{
    /**
     * Bootstrap the Steam Socialite provider.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $socialite = $this->app->make(Factory::class);

        $socialite->extend('steam', function ($app) use ($socialite) {
            $config = $app['config']['services.steam'];

            return $socialite->buildProvider(SteamProvider::class, $config);
        });
    }
}
