<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

/**
 * Class LineProvider
 * @package Laravel\Socialite\Two
 */
class LineProvider extends AbstractProvider implements ProviderInterface
{
    protected $apiUrl = 'https://api.line.me';

    protected $version = 'v2';

    protected $grant_type = 'authorization_code';

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://access.line.me/dialog/oauth/weblogin', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return "{$this->apiUrl}/{$this->version}/oauth/accessToken";
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $profileUrl = "{$this->apiUrl}/{$this->version}/profile";

        $response = $this->getHttpClient()->get($profileUrl, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['userId'],
            'nickname' => null,
            'name' => $user['displayName'],
            'email' => null,
            'avatar' => $user['pictureUrl'],
            'avatar_original' => null,
            'profileUrl' => null,
        ]);
    }

    protected function getTokenFields($code)
    {
        $tokenFields = parent::getTokenFields($code);
        $tokenFields['grant_type'] = $this->grant_type;

        return $tokenFields;
    }

    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }
}