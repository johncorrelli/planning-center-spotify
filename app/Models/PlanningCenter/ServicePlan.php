<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;

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
     * @var [type]
     */
    public $id;

    /**
     * An array containing Song objects used for this ServicePlan's service.
     *
     * @var [type]
     */
    public $songs;

    /**
     * The API object used to connect to Planning Center.
     * This is inherited from PlanningCenter and is already authorized.
     *
     * @var Api
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
     * @param Api $api
     */
    public function __construct(int $serviceTypeId, object $servicePlan, Api $api)
    {
        $this->id = $servicePlan->id;
        $this->date = date('Y-m-d', strtotime($servicePlan->attributes->sort_date));

        $this->api = $api;
        $this->serviceTypeId = $serviceTypeId;

        $this->songs = $this->getSongs();
    }

    /**
     * Returns an array of the order of service items.
     *
     * @param int $planId
     *
     * @return array
     */
    public function getOrderOfService(int $planId): array
    {
        return $this->api->request(
            'GET',
            "service_types/{$this->serviceTypeId}/plans/{$planId}/items"
        )->data;
    }

    /**
     * Returns the only songs that contain links to Spotify.
     *
     * @return array
     */
    public function getSongLinks(): array
    {
        $spotifySongs = [];

        foreach ($this->songs as $song) {
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
    public function getSongsFromOrderOfService(array $orderOfService): array
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
