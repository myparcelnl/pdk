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
