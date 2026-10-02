<?php

namespace TruckersMP\SteamSocialite\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use TruckersMP\SteamSocialite\Models\OpenID;
use TruckersMP\SteamSocialite\Tests\Fakes\FakeOpenID;
use TruckersMP\SteamSocialite\Tests\TestCase;

class OpenIDTest extends TestCase
{
    public function testAuthUrlContainsNecessaryParameters()
    {
        $openID = new OpenID('http://auth.url');

        parse_str(explode('?', $openID->getAuthUrl($returnTo = 'http://return.url'))[1], $responseParams);

        $this->assertArrayHasKey('openid_ns', $responseParams);
        $this->assertArrayHasKey('openid_mode', $responseParams);
        $this->assertArrayHasKey('openid_return_to', $responseParams);
        $this->assertArrayHasKey('openid_identity', $responseParams);
        $this->assertArrayHasKey('openid_claimed_id', $responseParams);

        $this->assertSame($returnTo, $responseParams['openid_return_to']);
    }

    public function testAssertionIssuedForAnotherSiteIsRejected()
    {
        // The provider confirms the signature of every assertion.
        $handler = new MockHandler([new Response(200, [], "is_valid:true\n")]);
        $openID = new FakeOpenID('http://auth.url', new Client(['handler' => HandlerStack::create($handler)]));

        $assertion = [
            'openid_assoc_handle' => '1234567890',
            'openid_signed' => 'op_endpoint,claimed_id,return_to',
            'openid_sig' => 'signature',
            'openid_op_endpoint' => 'http://auth.url',
            'openid_claimed_id' => 'https://steamcommunity.com/openid/id/76561198012345678',
            'openid_return_to' => 'https://site.example/callback',
        ];

        $this->assertFalse($openID->validate(Request::create('https://attacker.example/callback', 'GET', $assertion)));
        $this->assertTrue($openID->validate(Request::create('https://site.example/callback', 'GET', $assertion)));
    }
}
