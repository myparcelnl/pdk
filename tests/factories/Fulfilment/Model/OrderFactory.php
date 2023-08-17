<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\RetailLocationFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Order
 * @method Order make()
 * @method $this withAccountId(int $accountId)
 * @method $this withCreatedAt(string $createdAt)
 * @method $this withDropOffPoint(RetailLocation|RetailLocationFactory $dropOffPoint)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withFulfilmentPartnerIdentifier(string $fulfilmentPartnerIdentifier)
 * @method $this withInvoiceAddress(ContactDetails|ContactDetailsFactory $invoiceAddress)
 * @method $this withLanguage(string $language)
 * @method $this withLines(OrderLineCollection|OrderLineFactory[] $lines)
 * @method $this withNotes(OrderNoteCollection|OrderNoteFactory[] $notes)
 * @method $this withOrderDate(string|DateTime $orderDate)
 * @method $this withPrice(int $price)
 * @method $this withPriceAfterVat(int $priceAfterVat)
 * @method $this withShipment(Shipment|ShipmentFactory $shipment)
 * @method $this withShopId(int $shopId)
 * @method $this withStatus(string $status)
 * @method $this withType(string $type)
 * @method $this withUpdatedAt(string $updatedAt)
 * @method $this withUuid(string $uuid)
 * @method $this withVat(int $vat)
 */
final class OrderFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Order::class;
    }
}
