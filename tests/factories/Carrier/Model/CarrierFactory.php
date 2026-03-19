<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use ArrayObject;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsInsuranceOptionV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Sdk\Support\Str;

use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Carrier
 * @method Carrier make()
 * @method $this withCarrier(string $carrier)
 * @method $this withPackageTypes(array $packageTypes)
 * @method $this withDeliveryTypes(array $deliveryTypes)
 * @method $this withOptions(array $options)
 * @method $this withTransactionTypes(array $transactionTypes)
 * @method $this withCollo(array $collo)
 */
final class CarrierFactory extends AbstractModelFactory
{
    /**
     * Set package types capability
     *
     * @param  array $types Array of CONSTANT_CASE package type names
     * @return $this
     */
    public function withCapabilityPackageTypes(array $types): self
    {
        return $this->withPackageTypes($types);
    }

    /**
     * Set delivery types capability
     *
     * @param  array $types Array of CONSTANT_CASE delivery type names
     * @return $this
     */
    public function withCapabilityDeliveryTypes(array $types): self
    {
        return $this->withDeliveryTypes($types);
    }

    /**
     * Set shipment options capability
     *
     * @param  array $options Array of shipment option configuration
     * @return $this
     */
    public function withCapabilityShipmentOptions(array $options): self
    {
        return $this->withOptions($options);
    }

    /**
     * Set collo capability
     *
     * @param int $max Maximum number of collos allowed for multi-collo capability
     * @return $this
     */
    public function withCapabilityMultiCollo(int $max): self
    {
        return $this->withCollo(['max' => $max]);
    }

    /**
     * Create carrier with all common capabilities.
     *
     * @TODO Remove permissive all-capabilities once CarrierSchema is replaced by capabilities-focused logic.
     *       CarrierSchema is @deprecated; real per-carrier capability constraints will come from the capabilities API.
     *
     * @param  string $carrier V2 carrier name to assign capabilities to
     * @return $this
     */
    public function withAllCapabilities(string $carrier = RefCapabilitiesSharedCarrierV2::POSTNL): self
    {
        $shipmentOptionsTypes = RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::openAPITypes();
        $allShipmentOptions   = [];

        foreach ($shipmentOptionsTypes as $key => $model) {
            $optionKey = Str::camel($key);
            // Insurance requires a populated insuredAmount; all other options can be empty arrays.
            // openAPITypes() returns class names with a leading backslash, while ::class does not — trim before comparing.
            if (ltrim($model, '\\') === RefCapabilitiesContractDefinitionsResponseOptionsInsuranceOptionV2::class) {
                $allShipmentOptions[$optionKey] = [
                    'insuredAmount' => [
                        'default' => ['currency' => 'EUR', 'amount' => 0],
                        'min'     => ['currency' => 'EUR', 'amount' => 0],
                        'max'     => ['currency' => 'EUR', 'amount' => 500000],
                    ],
                ];
            } else {
                $allShipmentOptions[$optionKey] = [];
            }
        }

        return $this
            ->withCarrier($carrier)
            ->withPackageTypes(RefShipmentPackageTypeV2::getAllowableEnumValues())
            ->withDeliveryTypes(RefTypesDeliveryTypeV2::getAllowableEnumValues())
            ->withCollo(['max' => 10])
            ->withOptions($allShipmentOptions);
    }

    /**
     * Create carrier with minimal capabilities (package + standard delivery only)
     *
     * @return $this
     */
    public function withMinimalCapabilities(): self
    {
        return $this
            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
            ->withPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
            ->withDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD]);
    }

    public function fromBpost(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::BPOST);
    }

    /**
     * @param  string $name
     *
     * @return self
     */
    public function fromCarrier(string $name): self
    {
        return $this->withCarrier($name);
    }

    public function fromDhlEuroplus(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS);
    }

    public function fromDhlForYou(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU);
    }

    public function fromDhlParcelConnect(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT);
    }

    public function fromDpd(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::DPD);
    }

    public function fromPostNL(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::POSTNL);
    }

    public function fromUpsStandard(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::UPS_STANDARD);
    }

    public function fromUpsExpressSaver(): self
    {
        return $this
            ->fromCarrier(RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER);
    }

    public function getModel(): string
    {
        return Carrier::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL);
    }
}
