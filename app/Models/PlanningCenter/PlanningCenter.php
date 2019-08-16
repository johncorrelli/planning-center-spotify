<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;

class PlanningCenter
{
    /**
     * An instance of the API object used to connect to Planning Center.
     *
     * @var Api
     */
    private $api;

    /**
     * Your application id used to connect to Planning Center's API.
     *
     * @var string
     */
    private $applicationId;

    /**
     * Your secret key used to connect to Planning Center's API.
     *
     * @var string
     */
    private $secretKey;

    /**
     * @param string $applicationId
     * @param string $secretKey
     * @param Api $api
     */
    public function __construct(string $applicationId, string $secretKey, Api $api)
    {
        $this->applicationId = $applicationId;
        $this->secretKey = $secretKey;
        $this->api = $api;

        $authorization = base64_encode("{$applicationId}:{$secretKey}");
        $this->api->setBaseUrl('https://api.planningcenteronline.com/services/v2');
        $this->api->setAuthorization("Authorization: Basic {$authorization}");
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
