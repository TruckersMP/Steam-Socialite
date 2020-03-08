<?php

namespace TruckersMP\SteamSocialite\Tests\Unit;

use Illuminate\Http\Request;
use Laravel\Socialite\Two\InvalidStateException;
use TruckersMP\SteamSocialite\Providers\SteamProvider;
use TruckersMP\SteamSocialite\Tests\TestCase;

class SteamSocialiteTest extends TestCase
{
    public function testInvalidRequestThrowsAnException()
    {
        $this->expectException(InvalidStateException::class);

        $request = Request::create('request');
        $provider = new SteamProvider($request, 'client_id', 'client_secret', 'redirect');

        $provider->user();
    }

    public function testRedirectUrlCanBeChanged()
    {
        $request = Request::create('request');
        $provider = new SteamProvider($request, 'client_id', 'client_secret', 'redirect');

        $response = $provider->redirect();
        parse_str(explode('?', $response->getTargetUrl())[1], $responseParams);

        $ownResponse = $provider->with(['redirect_uri' => 'http://redirect.url'])->redirect();
        parse_str(explode('?', $ownResponse->getTargetUrl())[1], $ownResponseParams);

        $this->assertNotSame($responseParams['openid_return_to'], $ownResponseParams['openid_return_to']);
    }
}
