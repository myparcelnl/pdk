<?php
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates main carrier by name', function () {
    $carrier = new Carrier(['name' => Carrier::CARRIER_POSTNL_NAME]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_POSTNL_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_MAIN);
});

it('creates main carrier by carrierId', function () {
    $carrier = new Carrier(['id' => Carrier::CARRIER_BPOST_ID]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_BPOST_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_MAIN);
});

it('creates custom carrier by subscriptionId', function () {
    $carrier = new Carrier(['subscriptionId' => 10932623]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_DPD_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_CUSTOM);
});

it('returns complete carrier object', function () {
    $carrier = new Carrier(['id' => Carrier::CARRIER_POSTNL_ID]);

    expect(array_filter(Arr::dot($carrier->toArray()), function ($item) { return null !== $item; }))
        ->toEqual(
            [
                'id'                                           => Carrier::CARRIER_POSTNL_ID,
                'name'                                         => Carrier::CARRIER_POSTNL_NAME,
                'primary'                                      => true,
                'isDefault'                                    => false,
                'optional'                                     => false,
                'type'                                         => Carrier::TYPE_MAIN,
                'externalIdentifier'                           => 'postnl',
                'enabled'                                      => false,
                'capabilities.deliveryTypes.0'                 => 'morning',
                'capabilities.deliveryTypes.1'                 => 'standard',
                'capabilities.deliveryTypes.2'                 => 'evening',
                'capabilities.deliveryTypes.3'                 => 'pickup',
                'capabilities.features.labelDescriptionLength' => 45,
                'capabilities.packageTypes.0'                  => 'package',
                'capabilities.packageTypes.1'                  => 'mailbox',
                'capabilities.packageTypes.2'                  => 'letter',
                'capabilities.packageTypes.3'                  => 'digital_stamp',
                'capabilities.shipmentOptions.ageCheck'        => true,
                'capabilities.shipmentOptions.largeFormat'     => true,
                'capabilities.shipmentOptions.onlyRecipient'   => true,
                'capabilities.shipmentOptions.return'          => true,
                'capabilities.shipmentOptions.sameDayDelivery' => true,
                'capabilities.shipmentOptions.signature'       => true,
                'capabilities.shipmentOptions.insurance.0'     => 0,
                'capabilities.shipmentOptions.insurance.1'     => 10000,
                'capabilities.shipmentOptions.insurance.2'     => 25000,
                'capabilities.shipmentOptions.insurance.3'     => 50000,
                'capabilities.shipmentOptions.insurance.4'     => 100000,
                'capabilities.shipmentOptions.insurance.5'     => 150000,
                'capabilities.shipmentOptions.insurance.6'     => 200000,
                'capabilities.shipmentOptions.insurance.7'     => 250000,
                'capabilities.shipmentOptions.insurance.8'     => 300000,
                'capabilities.shipmentOptions.insurance.9'     => 350000,
                'capabilities.shipmentOptions.insurance.10'    => 400000,
                'capabilities.shipmentOptions.insurance.11'    => 450000,
                'capabilities.shipmentOptions.insurance.12'    => 500000,
            ]
        );
});

