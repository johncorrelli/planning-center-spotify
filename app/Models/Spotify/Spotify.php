<?php

namespace App\Models\Spotify;

use App\Models\Api;

class Spotify
{
    const PLAYLIST_DESCRIPTION = 'PlanningCenter weekly playlist';

    /**
     * @var string
     */
    public $userId;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var string
     */
    private $authToken;

    public function __construct(string $authToken, Api $api)
    {
        $this->authToken = $authToken;
        $this->api = $api;

        $this->api->setBaseUrl('https://api.spotify.com/v1');
        $this->api->setAuthorization("Authorization: Bearer {$authToken}");

        $this->setUserId();
    }

    /**
     * Create a new playlist in Spotify.
     *
     * @param string $playlistName The name of the new playlist
     *
     * @return string
     */
    public function createPlaylist(string $playlistName): string
    {
        $newPlaylist = $this->api->request(
            'POST',
            "users/{$this->userId}/playlists",
            [
                'name' => $playlistName,
                'description' => self::PLAYLIST_DESCRIPTION,
            ]
        );

        return $newPlaylist->id;
    }

    /**
     * Loops through the $services and creates (or updates) a playlist based off of the songs within the service.
     *
     * @param array $existingPlaylists
     * @param array $services
     */
    public function createPlaylists(array $existingPlaylists, array $services): void
    {
        foreach ($services as $service) {
            $playlistName = "Sunday Setlist: {$service->date}";
            $spotifySongs = $service->getSongLinks();

            if (count($spotifySongs) === 0) {
                continue;
            }

            $playlistId =
                $this->getPlaylistByName($existingPlaylists, $playlistName)
                ?? $this->createPlaylist($playlistName);

            $this->setPlaylistSongs($playlistId, $spotifySongs);

            echo "Created or Updated playlist: {$playlistName}.\n";
        }
    }

    /**
     * Returns the first 50 playlists for the current user.
     *
     * @return array
     */
    public function getPlaylists(): array
    {
        return $this->api->request('GET', 'me/playlists?limit=50')->items;
    }

    /**
     * Returns the current user.
     *
     * @return object`
     */
    public function me(): object
    {
        return $this->api->request('GET', 'me');
    }

    /**
     * Checks to see if a playlist already exists. The search is based off of name.
     *
     * @param array $playlists The current user's playlists
     * @param string $searchName The name to search
     *
     * @return null|string If exists, the `id` of the matching playlist
     */
    protected function getPlaylistByName(array $playlists, string $searchName): ?string
    {
        foreach ($playlists as $playlist) {
            if ($playlist->name !== $searchName) {
                continue;
            }

            return $playlist->id;
        }

        return null;
    }

    /**
     * Formats the links from a url to a Spotify song uri..
     *
     * @param array $songs
     *
     * @return array
     */
    protected function getUriFromLink(array $songs): array
    {
        return array_unique(array_map(function ($link) {
            $parts = explode('track/', $link);

            return "spotify:track:{$parts[1]}";
        }, $songs));
    }

    /**
     * Updates a playlist to the specified songs.
     *
     * @param string $playlistId
     * @param array $links The array of links to put into the spotify playlist
     */
    protected function setPlaylistSongs(string $playlistId, array $links): void
    {
        $uris = $this->getUriFromLink($links);

        $this->api->request(
            'PUT',
            "playlists/{$playlistId}/tracks",
            ['uris' => $uris]
        );
    }

    /**
     * Sets the current user id based off of the response from Spotify.
     */
    protected function setUserId(): void
    {
        $this->userId = $this->me()->id;
    }
}
