<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule;

use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
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

    /**
     * @var CarrierRepositoryInterface
     */
    private $carrierRepository;

    /**
     * @param  CarrierRepositoryInterface $carrierRepository
     */
    public function __construct(CarrierRepositoryInterface $carrierRepository)
    {
        $this->api               = new ShippingRuleApi($this->createGuzzleClient(), $this->getApiConfig());
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * Return the V2 carrier name implied by the shop's shipping rules, or null when unavailable.
     *
     * Fetches the first implication returned for the shop, reads its carrier_id, and maps
     * it to a V2 carrier name via CarrierRepository. Returns null when the implications list
     * is empty, carrier_id is absent, the id is unmapped, or the API call fails.
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
            $carrier = $this->carrierRepository->findByLegacyId((int) $carrierId);

            return $carrier ? $carrier->carrier : null;
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
