<?php

namespace App\Models\Spotify;

use App\Models\Api;

class SpotifyApi extends Api
{
    /**
     * @param string $authToken your temporary authorization token for connecting to Spotify
     * @param [type] ...$attrs
     */
    public function __construct(string $authToken, ...$attrs)
    {
        parent::__construct(...$attrs);

        $this->setBaseUrl('https://api.spotify.com/v1');
        $this->setAuthorization("Authorization: Bearer {$authToken}");
    }
}
