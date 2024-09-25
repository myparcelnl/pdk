<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use DateTimeInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties;
use MyParcelNL\Pdk\App\Order\Model\PdkPhysicalPropertiesFactory;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\App\Order\Model\UsesCurrency;
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
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHidden(bool $hidden)
 * @method $this withIsReturn(bool $isReturn)
 * @method $this withLinkConsumerPortal(string $linkConsumerPortal)
 * @method $this withModified(array|string|DateTimeInterface $modified)
 * @method $this withModifiedBy(int $modifiedBy)
 * @method $this withMultiCollo(bool $multiCollo)
 * @method $this withMultiColloMainShipmentId(string $multiColloMainShipmentId)
 * @method $this withOrderId(string $orderId)
 * @method $this withPartnerTrackTraces(array $partnerTrackTraces)
 * @method $this withPhysicalProperties(array|PdkPhysicalProperties|PdkPhysicalPropertiesFactory $physicalProperties)
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
    use UsesCurrency;

    public function getModel(): string
    {
        return Shipment::class;
    }

    /**
     * @return $this
     */
    public function withDeliveryOptionsWithPickupLocationInEU(): self
    {
        return $this->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withPickupLocation(factory(RetailLocation::class)->inEU())
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME)
        );
    }

    /**
     * @return $this
     */
    public function withDeliveryOptionsWithPickupLocationInTheNetherlands(): self
    {
        return $this->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withPickupLocation(factory(RetailLocation::class)->inTheNetherlands())
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME)
        );
    }

    /**
     * @param  array|RetailLocation|RetailLocationFactory $dropOffPoint
     *
     * @return $this
     */
    public function withDropOffPoint($dropOffPoint = null): self
    {
        return $this->with(['dropOffPoint' => $dropOffPoint ?? factory(RetailLocation::class)]);
    }

    /**
     * @param  null|int $id
     *
     * @return self
     */
    public function withId(?int $id = null): self
    {
        return $this->with(['id' => $id ?? $this->getNextId()]);
    }

    /**
     * @param  int|array|Currency|CurrencyFactory $price
     *
     * @return $this
     */
    public function withPrice($price): self
    {
        return $this->withCurrencyField('price', $price);
    }
}
