<?php

namespace App\Models\Spotify;

class Spotify
{
    const PLAYLIST_DESCRIPTION = 'PlanningCenter weekly playlist';

    /**
     * @var string
     */
    public $userId;

    /**
     * @var SpotifyApi
     */
    private $api;
    /**
     * @var array
     */
    private $existingPlaylists;

    public function __construct(SpotifyApi $api)
    {
        $this->api = $api;
        $this->setUserId();
        $this->existingPlaylists = $this->getPlaylists();
    }

    /**
     * Gets a playlist by name, if one does not exist, a new playlist is returned.
     *
     * @param string $playlistName
     *
     * @return object
     */
    public function getOrCreatePlaylistByName(string $playlistName): object
    {
        foreach ($this->existingPlaylists as $playlist) {
            if ($playlist->name !== $playlistName) {
                continue;
            }

            return $playlist;
        }

        return $this->createPlaylist($playlistName);
    }

    /**
     * Updates a playlist to the specified songs.
     *
     * @param string $playlistId
     * @param array $links The array of links to put into the spotify playlist
     */
    public function setPlaylistSongs(string $playlistId, array $links): void
    {
        $uris = $this->getUriFromLink($links);

        $this->api->request(
            'PUT',
            "playlists/{$playlistId}/tracks",
            ['uris' => $uris]
        );
    }

    /**
     * Create a new playlist in Spotify.
     *
     * @param string $playlistName The name of the new playlist
     *
     * @return object
     */
    protected function createPlaylist(string $playlistName): object
    {
        return $this->api->request(
            'POST',
            "users/{$this->userId}/playlists",
            [
                'name' => $playlistName,
                'description' => self::PLAYLIST_DESCRIPTION,
            ]
        );
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
     * Returns the first 50 playlists for the current user.
     *
     * @return array
     */
    protected function getPlaylists(): array
    {
        return $this->api->request('GET', 'me/playlists?limit=50')->items;
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
     * Returns the current user.
     *
     * @return object
     */
    protected function me(): object
    {
        return $this->api->request('GET', 'me');
    }

    /**
     * Sets the current user id based off of the response from Spotify.
     */
    protected function setUserId(): void
    {
        $this->userId = $this->me()->id;
    }
}
