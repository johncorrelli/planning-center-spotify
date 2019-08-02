<?php

namespace App\Models\Spotify;

use App\Models\Api;

class SpotifyAuthorization
{
    const RESPONSE_URI = 'https://postman-echo.com/post';

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $refreshToken;

    public function __construct(string $clientId, string $clientSecret, ?string $accessToken, ?string $refreshToken = null, Api $api)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->api = $api;

        $this->api->setBaseUrl('https://accounts.spotify.com/api');
        $this->api->setPostBodyFormat('application/x-www-form-urlencoded');
    }

    /**
     * Generates an authorization token for the current user.
     * If the user has never initialized Spotify, it will first
     * take them through the authorization flow.
     *
     * @return string
     */
    public function generateAuthToken(): string
    {
        if (!$this->getAccessToken()) {
            return $this->initializeAuthorization();
        }

        return $this->refreshAccessToken($this->getRefreshToken());
    }

    /**
     * Returns the current access token.
     *
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Returns the refresh token, used to generate a new access token.
     *
     * @return null|string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Only needs to run once per user (unless they clear the refresh token).
     * Will generate and access and refresh token for the current user.
     * They will be asked to paste a `code` into the terminal, which we will then
     * use to generate their access and refresh tokens.
     *
     * @return string
     */
    public function initializeAuthorization(): string
    {
        $responseUri = self::RESPONSE_URI;

        echo "Visit: https://accounts.spotify.com/authorize?client_id={$this->clientId}".
            "&redirect_uri={$responseUri}",
            '&scope=playlist-read-collaborative,playlist-modify-public'.
            '&response_type=code&state=oolala';

        echo "\n\n";

        echo "After authorization, you will be redirected to {$responseUri} \n";
        echo "Copy the `code` from the query hash paste below.\n";
        echo "\n";

        echo 'Code: ';
        $authorizationCode = readline();

        return $this->getAccessTokenFromAuthorizationCode($authorizationCode);
    }

    /**
     * After a user enters the `code`, we will send that off to Spotify to hydrate
     * the access and refresh token. Access tokens last for 1 hour. Refresh tokens are forever.
     *
     * @param string $authorizationCode
     *
     * @return string
     */
    protected function getAccessTokenFromAuthorizationCode(string $authorizationCode): string
    {
        $authorization = base64_encode("{$this->clientId}:{$this->clientSecret}");

        $getCredentials = $this->api->request(
            'POST',
            'token',
            [
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => self::RESPONSE_URI,
            ],
            [
                "Authorization: Basic {$authorization}",
            ]
        );

        $accessToken = $getCredentials->access_token;
        $refreshToken = $getCredentials->refresh_token;

        $this->setAccessToken($accessToken);
        $this->setRefreshToken($refreshToken);

        return $accessToken;
    }

    /**
     * Generates a new access token based off of the user's refresh token.
     *
     * @param string $refreshToken
     *
     * @return string
     */
    protected function refreshAccessToken(string $refreshToken): string
    {
        $authorization = base64_encode("{$this->clientId}:{$this->clientSecret}");

        $getCredentials = $this->api->request(
            'POST',
            'token',
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
            [
                "Authorization: Basic {$authorization}",
            ]
        );

        $accessToken = $getCredentials->access_token;
        $this->setAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * Sets the new access toekn.
     *
     * @param string $accessToken
     */
    protected function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Sets the refresh token.
     *
     * @param string $refreshToken
     */
    protected function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}
