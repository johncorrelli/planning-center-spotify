<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;
use DateTimeImmutable;

class PlanningCenter
{
    /**
     * int The number of future services to sync on a given run.
     */
    const NUM_SERVICES = 4;

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
     * The id of your service type in Planning Center.
     *
     * @var int
     */
    private $serviceTypeId;

    /**
     * @param string $applicationId
     * @param string $secretKey
     * @param int $serviceTypeId
     * @param Api $api
     */
    public function __construct(string $applicationId, string $secretKey, int $serviceTypeId, Api $api)
    {
        $this->applicationId = $applicationId;
        $this->secretKey = $secretKey;
        $this->serviceTypeId = $serviceTypeId;
        $this->api = $api;

        $authorization = base64_encode("{$applicationId}:{$secretKey}");
        $this->api->setBaseUrl('https://api.planningcenteronline.com/services/v2');
        $this->api->setAuthorization("Authorization: Basic {$authorization}");
    }

    /**
     * Returns upcoming ServicePlans.
     *
     * @param DateTimeImmutable $servicesAfterDate Returns services after this date
     *
     * @return array
     */
    public function getServicePlansAfter(DateTimeImmutable $servicesAfterDate): array
    {
        $after = $servicesAfterDate->format('Y-m-d');
        $numServices = self::NUM_SERVICES;
        $serviceTypeId = $this->serviceTypeId;

        $services = $this->api->request(
            'GET',
            "service_types/{$this->serviceTypeId}/plans?filter=after&after={$after}&per_page={$numServices}"
        )->data;

        return array_map(function ($service) use ($serviceTypeId) {
            return new ServicePlan($serviceTypeId, $service, $this->api);
        }, $services);
    }
}
