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
 * @method $this withSubscriptionId(int $subscriptionId)
 * @method $this withType(string $type)
 */
final class CarrierFactory extends AbstractModelFactory
{
    public function fromBpost(): self
    {
        return $this
            ->withName(Carrier::CARRIER_BPOST_NAME)
            ->withId(Carrier::CARRIER_BPOST_ID);
    }

    public function fromDhlEuroplus(): self
    {
        return $this
            ->withName(Carrier::CARRIER_DHL_EUROPLUS_NAME)
            ->withId(Carrier::CARRIER_DHL_EUROPLUS_ID);
    }

    public function fromDhlForYou(): self
    {
        return $this
            ->withName(Carrier::CARRIER_DHL_FOR_YOU_NAME)
            ->withId(Carrier::CARRIER_DHL_FOR_YOU_ID);
    }

    public function fromDhlParcelConnect(): self
    {
        return $this
            ->withName(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME)
            ->withId(Carrier::CARRIER_DHL_PARCEL_CONNECT_ID);
    }

    public function fromDpd(): self
    {
        return $this
            ->withName(Carrier::CARRIER_DPD_NAME)
            ->withId(Carrier::CARRIER_DPD_ID);
    }

    public function fromPostNL(): self
    {
        return $this
            ->withName(Carrier::CARRIER_POSTNL_NAME)
            ->withId(Carrier::CARRIER_POSTNL_ID);
    }

    public function fromUps(): self
    {
        return $this
            ->withName(Carrier::CARRIER_UPS_NAME)
            ->withId(Carrier::CARRIER_UPS_ID);
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
