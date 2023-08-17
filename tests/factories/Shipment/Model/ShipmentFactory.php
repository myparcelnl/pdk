<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Shipment
 * @method Shipment make()
 * @method $this withBarcode(string $barcode)
 * @method $this withCarrier(Carrier|CarrierFactory $carrier)
 * @method $this withCollectionContact(string $collectionContact)
 * @method $this withCreated(string|DateTime $created)
 * @method $this withCreatedBy(int $createdBy)
 * @method $this withCustomsDeclaration(CustomsDeclaration|CustomsDeclarationFactory|CustomsDeclarationFactory|CustomsDeclarationFactory $customsDeclaration)
 * @method $this withDelayed(bool $delayed)
 * @method $this withDeleted(string|DateTime $deleted)
 * @method $this withDelivered(bool $delivered)
 * @method $this withDeliveryOptions(DeliveryOptions|DeliveryOptionsFactory|DeliveryOptionsFactory|DeliveryOptionsFactory $deliveryOptions)
 * @method $this withDropOffPoint(RetailLocation|RetailLocationFactory|RetailLocationFactory $dropOffPoint)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHidden(bool $hidden)
 * @method $this withId(int $id)
 * @method $this withIsReturn(bool $isReturn)
 * @method $this withLinkConsumerPortal(string $linkConsumerPortal)
 * @method $this withModified(string|DateTime $modified)
 * @method $this withModifiedBy(int $modifiedBy)
 * @method $this withMultiCollo(bool $multiCollo)
 * @method $this withMultiColloMainShipmentId(string $multiColloMainShipmentId)
 * @method $this withOrderId(string $orderId)
 * @method $this withPartnerTrackTraces(array $partnerTrackTraces)
 * @method $this withPhysicalProperties(PhysicalProperties|PhysicalPropertiesFactory $physicalProperties)
 * @method $this withPrice(Currency|CurrencyFactory $price)
 * @method $this withRecipient(ShippingAddress|ShippingAddressFactory $recipient)
 * @method $this withReferenceIdentifier(string $referenceIdentifier)
 * @method $this withSender(ContactDetails|ContactDetailsFactory $sender)
 * @method $this withShipmentType(int $shipmentType)
 * @method $this withShopId(int $shopId)
 * @method $this withStatus(int $status)
 * @method $this withUpdated(string|DateTime $updated)
 */
final class ShipmentFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Shipment::class;
    }
}
