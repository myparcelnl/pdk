<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\AbstractCoreApiPrivateService;
use MyParcelNL\Sdk\Client\Generated\CoreApiPrivate\Api\ShippingRuleApi;
use MyParcelNL\Sdk\Client\Generated\CoreApiPrivate\ApiException;
use Throwable;

/**
 * Retrieves shipping rule implications for a shop and extracts the default carrier name.
 */
class ImplicationsService extends AbstractCoreApiPrivateService
{
    /**
     * @var ShippingRuleApi
     */
    private $api;

    public function __construct()
    {
        $this->api = new ShippingRuleApi($this->createGuzzleClient(), $this->getApiConfig());
    }

    /**
     * Return the V2 carrier name implied by the shop's shipping rules, or null when unavailable.
     *
     * Fetches the first implication returned for the shop, reads its carrier_id, and translates
     * it to a V2 carrier name via {@see Carrier::v2NameFromLegacyId()}. Returns null when the
     * implications list is empty, carrier_id is absent, the id is not in the local mapping, or
     * the API call fails.
     *
     * The returned name is not checked against the shop's available carrier contracts — that
     * "is this carrier actually available to my shop?" check is the caller's responsibility,
     * since it is the caller that holds the authoritative carrier collection (the just-fetched
     * contract definitions are not yet persisted at the moment this service is consulted).
     *
     * @param  int $shopId
     *
     * @return null|string V2 carrier name (e.g. "POSTNL"), or null on any failure path.
     */
    public function getDefaultCarrierName(int $shopId): ?string
    {
        try {
            $response     = $this->api->getShippingRuleImplications($shopId);
            $implications = $response->getData()->getImplications();

            if (empty($implications)) {
                return null;
            }

            // getCarrierId() is declared as RefTypesCarrier (an integer enum), but the underlying
            // container holds a raw int or null when carrier_id is absent from the API response.
            $carrierId = $implications[0]->getCarrierId();

            // @phpstan-ignore identical.alwaysFalse (carrier_id container value is a raw int or null at runtime; RefTypesCarrier is a constant-only enum class that is never instantiated)
            if ($carrierId === null) {
                return null;
            }

            // @phpstan-ignore cast.int (same reason: container holds a raw int, not an instantiated RefTypesCarrier)
            return Carrier::v2NameFromLegacyId((int) $carrierId);
        } catch (ApiException $e) {
            // Expected SDK request failure — LoggingMiddleware already logged the HTTP error
            // at the Guzzle transport layer before the exception was rethrown.
            return null;
        } catch (Throwable $e) {
            // Unexpected error (TypeError on unexpected response shape, undefined offset, etc.).
            // Log so programmer errors don't get silently masked.
            Logger::error('Unexpected error fetching default carrier name', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getApiClients(): array
    {
        return [$this->api];
    }
}
