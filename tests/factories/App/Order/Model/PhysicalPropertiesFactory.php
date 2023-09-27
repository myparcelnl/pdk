<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PhysicalProperties
 * @method ShippingAddress make()
 * @method $this withWeight(int $weight)
 * @method $this withLength(int $length)
 * @method $this withWidth(int $width)
 * @method $this withHeight(int $height)
 */
final class ShippingAddressFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShippingAddress::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withWeight(0);
    }
}
