<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

abstract class AbstractPdkOrderOptionCalculator implements PdkOrderOptionCalculatorInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    protected $order;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Build the capabilities recipient for this order's shipping address.
     *
     * The recipient is always known at order-processing time, so the business flag is always sent
     * explicitly (true or false) — capabilities are then resolved for the correct B2B/B2C context.
     *
     * @return array{country_code: null|string, is_business: bool}
     */
    protected function capabilitiesRecipient(): array
    {
        $address = $this->order->shippingAddress;

        return [
            'country_code' => $address->cc,
            'is_business'  => $address->isBusiness,
        ];
    }

    /**
     * Fetch the capability for the order's carrier, destination, package type and delivery type.
     * Null when the order has no country code or package type, or when the carrier has no capability for it.
     *
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2
     */
    protected function fetchShipmentCapability(): ?RefCapabilitiesResponseCapabilityV2
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $carrierName     = $deliveryOptions->carrier->carrier;
        $v2PackageType   = ApiMapperService::forPackageType()->v2NameFromLegacyName((string) $deliveryOptions->packageType);
        $v2DeliveryType  = ApiMapperService::forDeliveryType()->v2NameFromLegacyName((string) $deliveryOptions->deliveryType);

        if (! $this->order->shippingAddress->cc || ! $v2PackageType) {
            return null;
        }

        $args = [
            'carrier'      => $carrierName,
            'recipient'    => $this->capabilitiesRecipient(),
            'package_type' => $v2PackageType,
        ];

        if ($v2DeliveryType) {
            $args['delivery_type'] = $v2DeliveryType;
        }

        /** @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService $capabilitiesService */
        $capabilitiesService = Pdk::get(CapabilitiesValidationService::class);

        return $capabilitiesService->indexByCarrier(
            $capabilitiesService->getRepository()->getCapabilities($args)
        )[$carrierName] ?? null;
    }
}
