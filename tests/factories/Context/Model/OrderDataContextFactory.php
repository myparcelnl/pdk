<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use DateTimeImmutable;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLineFactory;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationFactory;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptionsFactory;
use MyParcelNL\Pdk\Shipment\Model\ShipmentFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of OrderDataContext
 * @method OrderDataContext make()
 * @method $this withApiIdentifier(string $apiIdentifier)
 * @method $this withBillingAddress(ContactDetails|ContactDetailsFactory|ContactDetailsFactory $billingAddress)
 * @method $this withCustomsDeclaration(CustomsDeclaration|CustomsDeclarationFactory|CustomsDeclarationFactory $customsDeclaration)
 * @method $this withDeliveryOptions(DeliveryOptions|DeliveryOptionsFactory|DeliveryOptionsFactory $deliveryOptions)
 * @method $this withExported(bool $exported)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withLines(PdkOrderLineCollection|PdkOrderLineFactory[]|PdkOrderLineFactory[]|PdkOrderLineFactory[] $lines)
 * @method $this withNotes(PdkOrderNoteCollection $notes)
 * @method $this withOrderDate(string|DateTimeImmutable $orderDate)
 * @method $this withOrderPrice(int $orderPrice)
 * @method $this withOrderPriceAfterVat(int $orderPriceAfterVat)
 * @method $this withOrderVat(int $orderVat)
 * @method $this withSenderAddress(ContactDetails|ContactDetailsFactory|ContactDetailsFactory $senderAddress)
 * @method $this withShipmentPrice(int $shipmentPrice)
 * @method $this withShipmentPriceAfterVat(int $shipmentPriceAfterVat)
 * @method $this withShipmentVat(int $shipmentVat)
 * @method $this withShipments(ShipmentCollection|ShipmentFactory[]|ShipmentFactory[] $shipments)
 * @method $this withShippingAddress(ShippingAddress|ShippingAddressFactory|ShippingAddressFactory $shippingAddress)
 * @method $this withTotalPrice(int $totalPrice)
 * @method $this withTotalPriceAfterVat(int $totalPriceAfterVat)
 * @method $this withTotalVat(int $totalVat)
 */
final class OrderDataContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return OrderDataContext::class;
    }
}
