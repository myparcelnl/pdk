<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;

dataset('multicolloPdkOrders', [
    // collo.max > 1 → CarrierValidationService::supportsMultiCollo() returns true → single shipment with secondary_shipments.
    // The default account carrier (POSTNL with withAllCapabilities, collo max 10) is used as-is.
    'real multicollo order' => [
        'factory'                   => function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(5)
                            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    )
            );
        },
        'expectedNumberOfShipments' => 1,
    ],

    // collo.max <= 1 → CarrierValidationService::supportsMultiCollo() returns false → separate shipment per label.
    // Directly updates the stored account's POSTNL carrier to have collo max 1, bypassing the factory
    // chain which would re-apply withAllCarriers() and reset collo to max 10.
    'fake multicollo' => [
        'factory'                   => function () {
            /** @var PdkAccountRepositoryInterface $accountRepository */
            $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
            $account           = $accountRepository->getAccount();

            $account->shops->first()->carriers = new CarrierCollection([
                factory(Carrier::class)->fromPostNL()->withCollo(['max' => 1])->make(),
            ]);

            $accountRepository->store($account);

            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(2)
                            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    )
            );
        },
        'expectedNumberOfShipments' => 2,
    ],

    // Separate pickup regression fixture: five independently shippable 6 kg lines for five labels.
    'real pickup multicollo order' => [
        'factory'                   => function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(5)
                            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                            ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME)
                            ->withPickupLocation(factory(RetailLocation::class)->inTheNetherlands())
                    )
                    ->withLines(array_map(
                        static function () {
                            return factory(PdkOrderLine::class)
                                ->withProduct(factory(PdkProduct::class)->withWeight(6000));
                        },
                        range(1, 5)
                    ))
            );
        },
        'expectedNumberOfShipments' => 1,
    ],

    // Separate pickup regression fixture: two independently shippable 15 kg lines for two labels.
    'fake pickup multicollo' => [
        'factory'                   => function () {
            /** @var PdkAccountRepositoryInterface $accountRepository */
            $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
            $account           = $accountRepository->getAccount();

            $account->shops->first()->carriers = new CarrierCollection([
                factory(Carrier::class)->fromPostNL()->withCollo(['max' => 1])->make(),
            ]);

            $accountRepository->store($account);

            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(2)
                            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                            ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME)
                            ->withPickupLocation(factory(RetailLocation::class)->inTheNetherlands())
                    )
                    ->withLines(array_map(
                        static function () {
                            return factory(PdkOrderLine::class)
                                ->withProduct(factory(PdkProduct::class)->withWeight(15000));
                        },
                        range(1, 2)
                    ))
            );
        },
        'expectedNumberOfShipments' => 2,
    ],
]);
