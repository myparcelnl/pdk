<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Carrier
 * @method Carrier make()
 * @method $this withCapabilities(array|CarrierCapabilities|CarrierCapabilitiesFactory $capabilities)
 * @method $this withEnabled(bool $enabled)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHuman(string $human)
 * @method $this withId(int $id)
 * @method $this withIsDefault(bool $isDefault)
 * @method $this withLabel(string $label)
 * @method $this withName(string $name)
 * @method $this withOptional(bool $optional)
 * @method $this withPrimary(bool $primary)
 * @method $this withReturnCapabilities(array|CarrierCapabilities|CarrierCapabilitiesFactory $returnCapabilities)
 * @method $this withContractId(int $contractId)
 * @method $this withType(string $type)
 */
final class CarrierFactory extends AbstractModelFactory
{
    public function fromBpost(): self
    {
        return $this
            ->withId(Carrier::CARRIER_BPOST_ID)
            ->fromCarrier(Carrier::CARRIER_BPOST_NAME);
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
            ->withHuman($name)
            ->withCapabilities(factory(CarrierCapabilities::class)->fromCarrier($name))
            ->withReturnCapabilities(factory(CarrierCapabilities::class)->fromCarrier($name));
    }

    public function fromDhlEuroplus(): self
    {
        return $this
            ->withId(Carrier::CARRIER_DHL_EUROPLUS_ID)
            ->fromCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME);
    }

    public function fromDhlForYou(): self
    {
        return $this
            ->withId(Carrier::CARRIER_DHL_FOR_YOU_ID)
            ->fromCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME);
    }

    public function fromDhlParcelConnect(): self
    {
        return $this
            ->withId(Carrier::CARRIER_DHL_PARCEL_CONNECT_ID)
            ->fromCarrier(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME);
    }

    public function fromDpd(): self
    {
        return $this
            ->withId(Carrier::CARRIER_DPD_ID)
            ->fromCarrier(Carrier::CARRIER_DPD_NAME);
    }

    public function fromPostNL(): self
    {
        return $this
            ->withId(Carrier::CARRIER_POSTNL_ID)
            ->fromCarrier(Carrier::CARRIER_POSTNL_NAME);
    }

    public function fromUps(): self
    {
        return $this
            ->withId(Carrier::CARRIER_UPS_ID)
            ->fromCarrier(Carrier::CARRIER_UPS_NAME);
    }

    public function getModel(): string
    {
        return Carrier::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withName(Carrier::CARRIER_POSTNL_NAME)
            ->withEnabled(true)
            ->withCapabilities(factory(CarrierCapabilities::class));
    }
}
