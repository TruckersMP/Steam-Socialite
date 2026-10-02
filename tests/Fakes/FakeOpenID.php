<?php

namespace TruckersMP\SteamSocialite\Tests\Fakes;

use GuzzleHttp\Client;
use TruckersMP\SteamSocialite\Models\OpenID;

class FakeOpenID extends OpenID
{
    /**
     * Create a new OpenID model with the given HTTP client.
     *
     * @param  string  $authUrl
     * @param  Client  $httpClient
     * @return void
     */
    public function __construct(string $authUrl, Client $httpClient)
    {
        parent::__construct($authUrl);

        $this->httpClient = $httpClient;
    }
}
