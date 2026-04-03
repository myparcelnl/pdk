<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;

dataset('multicolloPdkOrders', [
    // collo.max > 1 → CarrierSchema::canHaveMultiCollo() returns true → single shipment with secondary_shipments.
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

    // collo.max <= 1 → CarrierSchema::canHaveMultiCollo() returns false → separate shipment per label.
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
]);
