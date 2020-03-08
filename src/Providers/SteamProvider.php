<?php

namespace TruckersMP\SteamSocialite\Providers;

use Illuminate\Support\Arr;
use Laravel\Socialite\Contracts\User as UserContract;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use TruckersMP\SteamSocialite\Models\OpenID;

class SteamProvider extends AbstractProvider
{
    /**
     * Steam OpenID URL for authorization.
     */
    protected const OPENID_URL = 'https://steamcommunity.com/openid/login';

    /**
     * Steam API URL for fetching the user data.
     */
    protected const USER_INFO_URL = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002';

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * The instance of the OpenID model.
     *
     * @var OpenID
     */
    protected $openID;

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state): string
    {
        return $this->getOpenID()->getAuthUrl($this->getCodeFields($state)['redirect_uri']);
    }

    /**
     * Get the validation URL for the provider.
     *
     * In case of OpenID, which is stateless and does not use tokens, return the URL of
     * the page where the validation of the request is proceeded.
     *
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->getOpenID()->getValidationUrl();
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @return UserContract
     */
    public function user(): UserContract
    {
        if (!$this->isValid() || $this->getSteamId() === 0) {
            throw new InvalidStateException();
        }

        return $this->mapUserToObject($this->getUserByToken($this->getSteamId()));
    }

    /**
     * Get the raw user for the given access token.
     *
     * As Steam uses OpenID, no token is passed. Instead, the Steam ID is used.
     *
     * @param string $token
     *
     * @return array
     */
    protected function getUserByToken($token): array
    {
        $params = [
            'key' => $this->clientSecret,
            'steamids' => $token,
        ];

        $userUrl = self::USER_INFO_URL . '?' . http_build_query($params);

        $response = $this->getHttpClient()->get($userUrl);

        $user = json_decode($response->getBody()->getContents(), true);

        return Arr::get($user, 'response.players.0');
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return User
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => (int) Arr::get($user, 'steamid'),
            'nickname' => Arr::get($user, 'personaname'),
            'name' => Arr::get($user, 'realname'),
            'email' => null,
            'avatar' => Arr::get($user, 'avatarfull'),
        ]);
    }

    /**
     * Determine whether the request is valid.
     *
     * @return bool
     */
    protected function isValid(): bool
    {
        return $this->getOpenID()->validate($this->request);
    }

    /**
     * Get parsed user's Steam ID from the response.
     *
     * @return int
     */
    protected function getSteamId(): int
    {
        preg_match('#/id/([0-9]{17})#', $this->request->get('openid_claimed_id'), $matches);

        return $matches[1] ?? 0;
    }

    /**
     * Get the instance of the OpenID model.
     *
     * @return OpenID
     */
    protected function getOpenID(): OpenID
    {
        if ($this->openID !== null) {
            return $this->openID;
        }

        return $this->openID = new OpenID(self::OPENID_URL);
    }
}
