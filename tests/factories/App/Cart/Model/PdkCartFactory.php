<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLineFactory;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethodFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PdkCart
 * @method PdkCart make()
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withLines(PdkOrderLineCollection|PdkOrderLineFactory[] $lines)
 * @method $this withOrderPrice(int $orderPrice)
 * @method $this withOrderPriceAfterVat(int $orderPriceAfterVat)
 * @method $this withOrderVat(int $orderVat)
 * @method $this withShipmentPrice(int $shipmentPrice)
 * @method $this withShipmentPriceAfterVat(int $shipmentPriceAfterVat)
 * @method $this withShipmentVat(int $shipmentVat)
 * @method $this withShippingMethod(PdkShippingMethod|PdkShippingMethodFactory $shippingMethod)
 * @method $this withTotalPrice(int $totalPrice)
 * @method $this withTotalPriceAfterVat(int $totalPriceAfterVat)
 * @method $this withTotalVat(int $totalVat)
 */
final class PdkCartFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkCart::class;
    }
}
