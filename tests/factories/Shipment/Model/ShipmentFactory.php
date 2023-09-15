<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use DateTimeInterface;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Shipment
 * @method Shipment make()
 * @method $this withBarcode(string $barcode)
 * @method $this withCarrier(array|Carrier|CarrierFactory $carrier)
 * @method $this withCollectionContact(string $collectionContact)
 * @method $this withCreated(array|string|DateTimeInterface $created)
 * @method $this withCreatedBy(int $createdBy)
 * @method $this withCustomsDeclaration(array|CustomsDeclaration|CustomsDeclarationFactory|CustomsDeclarationFactory|CustomsDeclarationFactory $customsDeclaration)
 * @method $this withDelayed(bool $delayed)
 * @method $this withDeleted(string|DateTime $deleted)
 * @method $this withDelivered(bool $delivered)
 * @method $this withDeliveryOptions(array|DeliveryOptions|DeliveryOptionsFactory|DeliveryOptionsFactory|DeliveryOptionsFactory $deliveryOptions)
 * @method $this withDropOffPoint(array|RetailLocation|RetailLocationFactory|RetailLocationFactory $dropOffPoint)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHidden(bool $hidden)
 * @method $this withId(int $id)
 * @method $this withIsReturn(bool $isReturn)
 * @method $this withLinkConsumerPortal(string $linkConsumerPortal)
 * @method $this withModified(array|string|DateTimeInterface $modified)
 * @method $this withModifiedBy(int $modifiedBy)
 * @method $this withMultiCollo(bool $multiCollo)
 * @method $this withMultiColloMainShipmentId(string $multiColloMainShipmentId)
 * @method $this withOrderId(string $orderId)
 * @method $this withPartnerTrackTraces(array $partnerTrackTraces)
 * @method $this withPhysicalProperties(array|PhysicalProperties|PhysicalPropertiesFactory $physicalProperties)
 * @method $this withRecipient(array|ShippingAddress|ShippingAddressFactory $recipient)
 * @method $this withReferenceIdentifier(string $referenceIdentifier)
 * @method $this withSender(array|ContactDetails|ContactDetailsFactory $sender)
 * @method $this withShipmentType(int $shipmentType)
 * @method $this withShopId(int $shopId)
 * @method $this withStatus(int $status)
 * @method $this withUpdated(array|string|DateTime $updated)
 */
final class ShipmentFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Shipment::class;
    }

    /**
     * @param  int|array|Currency|CurrencyFactory $price
     */
    public function withPrice($price): self
    {
        if (is_int($price)) {
            $price = factory(Currency::class)->withAmount($price);
        }

        return $this->with(['price' => $price]);
    }
}
