<?php

namespace TruckersMP\SteamSocialite\Models;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use TruckersMP\SteamSocialite\Contracts\OpenID as OpenIDContract;

class OpenID implements OpenIDContract
{
    /**
     * The definition of OpenID Authentication 2.0 request.
     */
    protected const OPENID_NS = 'http://specs.openid.net/auth/2.0';

    /**
     * The OpenID local identifier.
     */
    protected const OPENID_IDENTITY = 'http://specs.openid.net/auth/2.0/identifier_select';

    /**
     * The OpenID claimed identifier.
     */
    protected const OPENID_CLAIMED_ID = self::OPENID_IDENTITY;

    /**
     * The page where the authorization is proceeded.
     *
     * @var string
     */
    protected $authUrl;

    /**
     * The instance of a HTTP client.
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * Create a new model instance for OpenID interface.
     *
     * @param  string  $authUrl
     * @return void
     */
    public function __construct(string $authUrl)
    {
        $this->authUrl = $authUrl;
    }

    /**
     * Get the OpenID authorization URL.
     *
     * @param  string  $returnTo
     * @return string
     */
    public function getAuthUrl(string $returnTo): string
    {
        return $this->authUrl . '?' . http_build_query($this->getAuthParameters($returnTo));
    }

    /**
     * Get the OpenID validation URL.
     *
     * @return string
     */
    public function getValidationUrl(): string
    {
        return $this->authUrl;
    }

    /**
     * Validate the request parameters.
     *
     * @param  Request  $request
     * @return bool
     */
    public function validate(Request $request): bool
    {
        // All necessary parameters for the validation request must be presented.
        if (!$request->has(['openid_assoc_handle', 'openid_signed', 'openid_sig', 'openid_claimed_id'])) {
            return false;
        }

        // Create a POST request to the OpenID login page to validate that the forwarded
        // parameters really come from the server and they are not passed by some user.
        $response = $this->getHttpClient()->post($this->getValidationUrl(), [
            RequestOptions::FORM_PARAMS => $this->getValidationParameters($request),
        ]);

        // As the response content is just a plain text separated by new lines, it must
        // be parsed into a Fluent object filled with the data from the response.
        $result = $this->parseResult($response->getBody()->getContents());

        return $result->is_valid === 'true';
    }

    /**
     * Parse the OpenID response to a Fluent object.
     *
     * @param  string  $response
     * @return Fluent
     */
    protected function parseResult(string $response): Fluent
    {
        $parsed = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }

            $line = explode(':', $line, 2);
            $parsed[$line[0]] = $line[1];
        }

        return new Fluent($parsed);
    }

    /**
     * Get the request parameters for the authorization page.
     *
     * @param  string  $returnTo
     * @return array
     */
    protected function getAuthParameters(string $returnTo): array
    {
        return [
            'openid_ns' => self::OPENID_NS,
            'openid_mode' => 'checkid_setup',
            'openid_return_to' => $returnTo,
            'openid_identity' => self::OPENID_IDENTITY,
            'openid_claimed_id' => self::OPENID_CLAIMED_ID,
        ];
    }

    /**
     * Get parameters for the OpenID validation request.
     *
     * @param  Request  $request
     * @return array
     */
    protected function getValidationParameters(Request $request): array
    {
        $params = [
            'openid.assoc_handle' => $request->get('openid_assoc_handle'),
            'openid.signed' => $request->get('openid_signed'),
            'openid.sig' => $request->get('openid_sig'),
            'openid.ns' => self::OPENID_NS,
        ];

        foreach (explode(',', $request->get('openid_signed')) as $item) {
            $value = $request->get('openid_' . str_replace('.', '_', $item));
            $params['openid.' . $item] = $value;
        }

        $params['openid.mode'] = 'check_authentication';

        return $params;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if ($this->httpClient !== null) {
            return $this->httpClient;
        }

        return $this->httpClient = new Client();
    }
}
