<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\Iam;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Sdk\Client\Generated\IamApi\Api\DefaultApi;
use MyParcelNL\Sdk\Client\Generated\IamApi\ApiException;
use MyParcelNL\Sdk\Client\Generated\IamApi\Model\WhoamiGet200Response;
use Throwable;

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
        $this->iamApi = new DefaultApi(null, $this->getApiConfig());
    }

    /**
     * Call the IAM /whoami endpoint.
     *
     * @return WhoamiGet200Response
     * @throws ApiException When the API returns a non-2xx response
     * @throws Throwable    On any other transport error
     */
    public function getWhoami(): WhoamiGet200Response
    {
        $logContext = ['operation' => 'whoamiGet'];

        try {
            $response = $this->iamApi->whoamiGet();

            Logger::debug('Successfully called whoami', array_replace($logContext, [
                'accountId' => $response->getAccountId(),
            ]));

            return $response;
        } catch (ApiException $e) {
            Logger::error('Whoami API call failed', array_replace($logContext, [
                'error'           => $e->getMessage(),
                'code'            => $e->getCode(),
                'responseBody'    => $e->getResponseBody(),
                'responseHeaders' => $e->getResponseHeaders(),
            ]));

            throw $e;
        } catch (Throwable $e) {
            Logger::error('Whoami call failed', array_replace($logContext, [
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
            ]));

            throw $e;
        }
    }
}
