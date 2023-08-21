<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use DateTimeImmutable;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationFactory;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptionsFactory;
use MyParcelNL\Pdk\Shipment\Model\ShipmentFactory;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Concern\HasIncrementingId;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of PdkOrder
 * @method PdkOrder make()
 * @method $this withApiIdentifier(string $apiIdentifier)
 * @method $this withBillingAddress(array|ContactDetails|ContactDetailsFactory $billingAddress)
 * @method $this withCustomsDeclaration(array|CustomsDeclaration|CustomsDeclarationFactory $customsDeclaration)
 * @method $this withExported(bool $exported)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withOrderDate(string|DateTimeImmutable $orderDate)
 * @method $this withOrderPrice(int $orderPrice)
 * @method $this withOrderPriceAfterVat(int $orderPriceAfterVat)
 * @method $this withOrderVat(int $orderVat)
 * @method $this withSenderAddress(array|ContactDetails|ContactDetailsFactory $senderAddress)
 * @method $this withShipmentPrice(int $shipmentPrice)
 * @method $this withShipmentPriceAfterVat(int $shipmentPriceAfterVat)
 * @method $this withShipmentVat(int $shipmentVat)
 * @method $this withShippingAddress(array|ShippingAddress|ShippingAddressFactory $shippingAddress)
 * @method $this withTotalPrice(int $totalPrice)
 * @method $this withTotalPriceAfterVat(int $totalPriceAfterVat)
 * @method $this withTotalVat(int $totalVat)
 */
final class PdkOrderFactory extends AbstractModelFactory
{
    use HasIncrementingId;

    public function getModel(): string
    {
        return PdkOrder::class;
    }

    /**
     * @param  array|DeliveryOptions|DeliveryOptionsFactory $deliveryOptions
     *
     * @return self
     */
    public function withDeliveryOptions($deliveryOptions = null): self
    {
        return $this->with(['deliveryOptions' => $deliveryOptions ?? factory(DeliveryOptions::class)]);
    }

    public function withDeliveryOptionsWithAllOptions(): self
    {
        return $this->withDeliveryOptions(factory(DeliveryOptions::class)->withAllShipmentOptions());
    }

    /**
     * @param  int|array[]|PdkOrderLineCollection|PdkOrderLineFactory[] $lines
     *
     * @return self
     */
    public function withLines($lines = 1): self
    {
        return $this->withCollection('lines', $lines);
    }

    public function withNotes($notes = 1): self
    {
        return $this->withCollection('notes', $notes);
    }

    public function withShipments($shipments = 1): self
    {
        return $this->withCollection('shipments', $shipments, function (ShipmentFactory $factory) {
            return $factory
                ->withShopId(AccountSettings::getShop()->id)
                ->withOrderId($this->attributes->get('externalIdentifier'))
                ->withPrice($this->attributes->get('shipmentPrice'));
        });
    }

    public function withSimpleDeliveryOptions(): self
    {
        return $this->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withSignature(true)
                )
        );
    }

    protected function createDefault(): FactoryInterface
    {
        $dutchAddress = factory(ContactDetails::class)->inTheNetherlands();

        return $this
            ->withExternalIdentifier("PDK-{$this->getNextId()}")
            ->withBillingAddress($dutchAddress)
            ->withShippingAddress($dutchAddress)
            ->withOrderPrice(1000)
            ->withOrderPriceAfterVat(1210)
            ->withOrderVat(210)
            ->withShipmentPrice(100)
            ->withShipmentPriceAfterVat(121)
            ->withShipmentVat(21)
            ->withTotalPrice(1100)
            ->withTotalPriceAfterVat(1331)
            ->withTotalVat(231)
            ->withOrderDate('2030-01-01 12:00:00');
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $repository */
        $repository = Pdk::get(PdkOrderRepositoryInterface::class);

        $repository->update($model);
    }
}
