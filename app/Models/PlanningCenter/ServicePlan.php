<?php

namespace App\Models\PlanningCenter;

use App\Models\Spotify\Spotify;

class ServicePlan
{
    const SONG_TYPE = 'song';

    /**
     * The date of the ServicePlan.
     *
     * @var string
     */
    public $date;

    /**
     * The id of the ServicePlan.
     *
     * @var int
     */
    public $id;

    /**
     * The API object used to connect to Planning Center.
     * This is inherited from PlanningCenter and is already authorized.
     *
     * @var PlanningCenterApi
     */
    protected $api;

    /**
     * The id of the ServicePlan's type, used to fetch the items.
     *
     * @var int
     */
    protected $serviceTypeId;

    /**
     * @param int $serviceTypeId
     * @param object $servicePlan
     * @param PlanningCenterApi $api
     */
    public function __construct(int $serviceTypeId, object $servicePlan, PlanningCenterApi $api)
    {
        $this->id = $servicePlan->id;
        $this->date = date('Y-m-d', strtotime($servicePlan->attributes->sort_date));

        $this->api = $api;
        $this->serviceTypeId = $serviceTypeId;
    }

    /**
     * Syncs the Songs of this ServicePlan with a Spotify Playlist
     *
     * @param Spotify $spotify
     *
     * @return void
     */
    public function syncWithSpotify(Spotify $spotify): void
    {
        $songs = $this->getSongs();
        $spotifySongs = $this->getSongLinks($songs);

        if (count($spotifySongs) === 0) {
            return;
        }

        $playlistName = "Sunday Setlist: {$this->date}";

        $playlist = $spotify->getOrCreatePlaylistByName($playlistName);

        $spotify->setPlaylistSongs($playlist->id, $spotifySongs);
    }

    /**
     * Returns an array of the order of service items.
     *
     * @param int $planId
     *
     * @return array
     */
    protected function getOrderOfService(int $planId): array
    {
        return $this->api->request(
            'GET',
            "service_types/{$this->serviceTypeId}/plans/{$planId}/items"
        )->data;
    }

    /**
     * Returns the only songs that contain links to Spotify.
     *
     * @param array $songs The Songs
     *
     * @return array
     */
    protected function getSongLinks(array $songs): array
    {
        $spotifySongs = [];

        foreach ($songs as $song) {
            $spotifySongs = array_merge($spotifySongs, $song->spotifyLinks);
        }

        return $spotifySongs;
    }

    /**
     * Returns only the Songs from the ServicePlan.
     *
     * @param array $orderOfService
     *
     * @return array
     */
    protected function getSongsFromOrderOfService(array $orderOfService): array
    {
        $songItems = array_values(array_filter($orderOfService, function ($item) {
            return $item->attributes->item_type === self::SONG_TYPE;
        }));

        return array_map(
            function ($songItem) {
                return new Song($songItem->relationships->song->data->id, $this->api);
            },
            $songItems
        );
    }

    /**
     * Returns Songs for the ServicePlan.
     *
     * @return array
     */
    private function getSongs(): array
    {
        $orderOfService = $this->getOrderOfService($this->id);

        return $this->getSongsFromOrderOfService($orderOfService);
    }
}
