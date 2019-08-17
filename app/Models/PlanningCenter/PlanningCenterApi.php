<?php

namespace App\Models\PlanningCenter;

use App\Models\Api;

class PlanningCenterApi extends Api
{
    /**
     * @param string $applicationId your application id used to connect to Planning Center's API
     * @param string $secretKey your secret key used to connect to Planning Center's API
     * @param ...$attrs
     */
    public function __construct(string $applicationId, string $secretKey, ...$attrs)
    {
        parent::__construct(...$attrs);

        $authorization = base64_encode("{$applicationId}:{$secretKey}");
        $this->setBaseUrl('https://api.planningcenteronline.com/services/v2');
        $this->setAuthorization("Authorization: Basic {$authorization}");
    }
}
