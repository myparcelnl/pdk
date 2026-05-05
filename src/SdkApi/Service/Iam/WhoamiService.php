<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\Iam;

use MyParcelNL\Sdk\Client\Generated\IamApi\Api\DefaultApi;
use MyParcelNL\Sdk\Client\Generated\IamApi\Model\FixedPrincipal;
use Override;

/**
 * Service for calling the IAM /whoami endpoint.
 *
 * Returns information about the authenticated principal, including:
 * - Account ID and shop IDs
 * - Active feature flags (ORDER_NOTES, DIRECT_PRINTING, ORDER_MANAGEMENT, etc.)
 * - Fulfilment platform membership
 *
 * This service is the transport layer. Business logic (feature mapping, order mode
 * precedence rules) lives in {@see \MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService}.
 *
 * @see \MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService
 * @see \MyParcelNL\Sdk\Client\Generated\IamApi\Api\DefaultApi::whoamiGet()
 */
class WhoamiService extends AbstractIamApiService
{
    /**
     * @var DefaultApi
     */
    private $iamApi;

    public function __construct()
    {
        $this->iamApi = new DefaultApi($this->createGuzzleClient(), $this->getApiConfig());
    }

    protected function getApiClients(): array
    {
        return [$this->iamApi];
    }

    /**
     * Call the IAM /whoami endpoint.
     *
     * For logging, see {@see MyParcelNL\Pdk\SdkApi\Middleware\LoggingMiddleware}.
     *
     * @return FixedPrincipal
     */
    public function getWhoami(): FixedPrincipal
    {
        return $this->iamApi->whoamiGet();
    }
}
