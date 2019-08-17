<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;
use App\Models\Spotify\Spotify;
use DateTimeImmutable;

class ServiceType
{
    /**
     * int The number of future services to sync on a given run.
     */
    const NUM_SERVICES = 4;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var int
     */
    protected $id;

    /**
     * Handles the API response from Planning Center and creates a new object.
     *
     * @param object $serviceType
     * @param Api $api
     */
    public function __construct(object $serviceType, Api $api)
    {
        $this->id = $serviceType->id;
        $this->api = $api;
    }

    /**
     * Syncs upcoming ServicePlans with Spotify.
     *
     * @param Spotify $spotify
     */
    public function syncServicePlansWithSpotify(Spotify $spotify): void
    {
        $today = new DateTimeImmutable();
        $servicePlans = $this->getServicePlansAfter($today, self::NUM_SERVICES);

        foreach ($servicePlans as $servicePlan) {
            $servicePlan->syncWithSpotify($spotify);
        }
    }

    /**
     * Returns upcoming ServicePlans.
     *
     * @param DateTimeImmutable $servicesAfterDate Returns services after this date
     *
     * @return array
     */
    protected function getServicePlansAfter(DateTimeImmutable $servicesAfterDate, int $numServices): array
    {
        $after = $servicesAfterDate->format('Y-m-d');

        $services = $this->api->request(
            'GET',
            "service_types/{$this->id}/plans?filter=after&after={$after}&per_page={$numServices}"
        )->data;

        return array_map(function ($service) {
            return new ServicePlan($this->id, $service, $this->api);
        }, $services);
    }
}
