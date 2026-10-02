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
        $required = [
            'openid_assoc_handle',
            'openid_signed',
            'openid_sig',
            'openid_claimed_id',
            'openid_return_to',
            'openid_op_endpoint',
        ];

        if (!$request->has($required)) {
            return false;
        }

        // The assertion must have been issued by the OpenID provider used for its validation.
        if ($request->get('openid_op_endpoint') !== $this->authUrl) {
            return false;
        }

        // The OpenID provider only verifies the signature, so it must be checked here that
        // the assertion was issued for this request and not for another relying party.
        if (!$this->isValidReturnTo($request)) {
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
     * Determine whether the return URL of the assertion matches the current request.
     *
     * The scheme, host, port, and path must be the same, and all query parameters of the
     * return URL must be present in the current request with the same values.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function isValidReturnTo(Request $request): bool
    {
        $returnTo = parse_url((string) $request->get('openid_return_to'));
        $current = parse_url($request->url());

        if (!isset($returnTo['scheme'], $returnTo['host'], $current['scheme'], $current['host'])) {
            return false;
        }

        if ($this->normalizeUrl($returnTo) !== $this->normalizeUrl($current)) {
            return false;
        }

        parse_str($returnTo['query'] ?? '', $expectedQuery);
        $actualQuery = $request->query->all();

        foreach ($expectedQuery as $key => $value) {
            if (!array_key_exists($key, $actualQuery) || $actualQuery[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the normalized scheme, host, port, and path of the parsed URL.
     *
     * @param  array  $url
     * @return string
     */
    protected function normalizeUrl(array $url): string
    {
        $scheme = strtolower($url['scheme']);
        $port = $url['port'] ?? ($scheme === 'https' ? 443 : 80);

        return $scheme . '://' . strtolower($url['host']) . ':' . $port . rtrim($url['path'] ?? '', '/');
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
            'openid.ns' => self::OPENID_NS,
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $returnTo,
            'openid.identity' => self::OPENID_IDENTITY,
            'openid.claimed_id' => self::OPENID_CLAIMED_ID,
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
    protected function getHttpClient(): Client
    {
        if ($this->httpClient !== null) {
            return $this->httpClient;
        }

        return $this->httpClient = new Client();
    }
}
