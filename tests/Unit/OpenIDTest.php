<?php

namespace TruckersMP\SteamSocialite\Tests\Unit;

use TruckersMP\SteamSocialite\Models\OpenID;
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
}
