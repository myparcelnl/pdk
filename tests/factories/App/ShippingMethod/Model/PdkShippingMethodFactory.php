<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\AddressFactory;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\PackageTypeFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PdkShippingMethod
 * @method PdkShippingMethod make()
 * @method $this withAllowedPackageTypes(PackageTypeCollection|PackageTypeFactory[] $allowedPackageTypes)
 * @method $this withHasDeliveryOptions(bool $hasDeliveryOptions)
 * @method $this withId(string $id)
 * @method $this withIsEnabled(bool $isEnabled)
 * @method $this withMinimumDropOffDelay(int $minimumDropOffDelay)
 * @method $this withName(string $name)
 * @method $this withShippingAddress(Address|AddressFactory $shippingAddress)
 */
final class PdkShippingMethodFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkShippingMethod::class;
    }
}
