<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use ArrayObject;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsBaseOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsBaseOptionV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsInsuranceBaseInsuranceV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Sdk\Support\Str;

use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Carrier
 * @method Carrier make()
 * @method $this withEnabled(bool $enabled)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHuman(string $human)
 * @method $this withId(int $id)
 * @method $this withIsDefault(bool $isDefault)
 * @method $this withLabel(string $label)
 * @method $this withName(string $name)
 * @method $this withOptional(bool $optional)
 * @method $this withPrimary(bool $primary)
 * @method $this withContractId(int $contractId)
 * @method $this withType(string $type)
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
     * Create carrier with all common capabilities
     *
     * @return $this
     */
    public function withAllCapabilities(): self
    {
        // Get the mapping from option names to types.
        $shipmentOptionsTypes = RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::openAPITypes();
        // Now create an array where the key is a camelCased version of the setters key, and call the setter to get the default configuration for that option
        $allShipmentOptions = [];
        foreach ($shipmentOptionsTypes as $key => $model) {
            $optionKey = Str::camel($key);
            // Instantiate an empty model for most cases, but for insurance we want to provide some additional expected attributes
            $allShipmentOptions[$optionKey] = new $model();
            if ($model === RefCapabilitiesSharedOptionsInsuranceBaseInsuranceV2::class) {
                $allShipmentOptions[$optionKey] = new RefCapabilitiesSharedOptionsInsuranceBaseInsuranceV2(['max' => 500]);
            } else {
                $allShipmentOptions[$optionKey] = new $model();
            }
        }

        return $this
            ->withCarrier(RefTypesCarrierV2::POSTNL)
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
            ->withCarrier(RefTypesCarrierV2::POSTNL)
            ->withPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
            ->withDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD]);
    }

    public function fromBpost(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::BPOST);
    }

    /**
     * @param  string $name
     *
     * @return self
     */
    public function fromCarrier(string $name): self
    {
        return $this
            ->withName($name)
            ->withCarrier($name)
            ->withHuman($name);
    }

    public function fromDhlEuroplus(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::DHL_EUROPLUS);
    }

    public function fromDhlForYou(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::DHL_FOR_YOU);
    }

    public function fromDhlParcelConnect(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::DHL_PARCEL_CONNECT);
    }

    public function fromDpd(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::DPD);
    }

    public function fromPostNL(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::POSTNL);
    }

    public function fromUpsStandard(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::UPS_STANDARD);
    }

    public function fromUpsExpressSaver(): self
    {
        return $this
            ->fromCarrier(RefTypesCarrierV2::UPS_EXPRESS_SAVER);
    }

    public function getModel(): string
    {
        return Carrier::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withName(RefTypesCarrierV2::POSTNL)
            ->withCarrier(RefTypesCarrierV2::POSTNL)
            ->withExternalIdentifier(RefTypesCarrierV2::POSTNL);
    }
}
