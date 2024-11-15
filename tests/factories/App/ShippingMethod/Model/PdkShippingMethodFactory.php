<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\AddressFactory;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
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
 * @method $this withDescription(string $description)
 * @method $this withShippingAddress(Address|AddressFactory $shippingAddress)
 */
final class PdkShippingMethodFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkShippingMethod::class;
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkShippingMethodRepository $shippingMethodRepository */
        $shippingMethodRepository = Pdk::get(PdkShippingMethodRepositoryInterface::class);
        $shippingMethodRepository->add($model);
    }
}
