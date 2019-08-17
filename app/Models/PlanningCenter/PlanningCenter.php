<?php

namespace App\Models\PlanningCenter;

class PlanningCenter
{
    /**
     * An instance of the API object used to connect to Planning Center.
     *
     * @var PlanningCenterApi
     */
    private $api;

    /**
     * @param PlanningCenterApi $api
     */
    public function __construct(PlanningCenterApi $api)
    {
        $this->api = $api;
    }

    /**
     * Returns the ServiceTypes.
     *
     * @return array
     */
    public function getServiceTypes(): array
    {
        $serviceTypes = $this->api->request(
            'GET',
            'service_types'
        )->data;

        return array_map(function ($serviceType) {
            return new ServiceType($serviceType, $this->api);
        }, $serviceTypes);
    }
}
