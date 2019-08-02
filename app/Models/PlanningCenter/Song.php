<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;

class Song
{
    const SPOTIFY_ATTACHMENT_TYPE = 'AttachmentSpotify';

    /**
     * The Author of the original song.
     *
     * @var string
     */
    public $author;

    /**
     * The id of the Song.
     *
     * @var int
     */
    public $id;

    /**
     * The link to the file attachment.
     *
     * @var array
     */
    public $spotifyLinks;

    /**
     * The title of the Song.
     *
     * @var string
     */
    public $title;

    /**
     * The API object used to connect to Planning Center.
     * This is inherited from PlanningCenter and is already authorized.
     *
     * @var Api
     */
    protected $api;

    /**
     * @param int $songId
     * @param Api $api
     */
    public function __construct(int $songId, Api $api)
    {
        $this->id = $songId;

        $this->api = $api;

        $song = $this->getSong($songId);
        $this->title = $song->attributes->title;
        $this->author = $song->attributes->author;

        $this->spotifyLinks = $this->getSpotifyLinks();
    }

    /**
     * Returns the arrangements for the Song.
     *
     * @param int $songId
     *
     * @return array
     */
    public function getArrangements(int $songId): array
    {
        return $this->api->request(
            'GET',
            "songs/{$songId}/arrangements"
        )->data;
    }

    /**
     * Returns the details of the attached file.
     *
     * @param string $openPath
     *
     * @return object
     */
    public function getFileDetails(string $openPath): object
    {
        $baseUrl = $this->api->getBaseUrl();

        return $this->api->request(
            'POST',
            str_replace($baseUrl, '', $openPath)
        )->data;
    }

    /**
     * Returns the Song data.
     *
     * @param int $songId
     *
     * @return object
     */
    public function getSong(int $songId): object
    {
        return $this->api->request(
            'GET',
            "songs/{$songId}"
        )->data;
    }

    /**
     * Returns the attachments for each arrangement on the Song.
     *
     * @param int $songId
     * @param int $arrangementId
     *
     * @return object
     */
    public function getSongArrangementAttachments(int $songId, int $arrangementId): array
    {
        return $this->api->request(
            'GET',
            "songs/{$songId}/arrangements/{$arrangementId}/attachments"
        )->data;
    }

    /**
     * Returns an array of Spotify links for each song.
     *
     * @return array
     */
    public function getSpotifyLinks(): array
    {
        $attachments = [];
        $arrangements = $this->getArrangements($this->id);

        foreach ($arrangements as $arrangement) {
            $attachments = array_merge($attachments, $this->getSongArrangementAttachments($this->id, $arrangement->id));
        }

        $spotifyFiles = array_values(array_filter($attachments, function ($attachment) {
            if (!isset($attachment->attributes->pco_type)) {
                return false;
            }

            return $attachment->attributes->pco_type === self::SPOTIFY_ATTACHMENT_TYPE;
        }));

        return array_map(function ($spotifyFile) {
            // Posting to this url will return the url for the file
            $openPath = $spotifyFile->links->self.'/open';

            return $this->getFileDetails($openPath)->attributes->attachment_url;
        }, $spotifyFiles);
    }
}
