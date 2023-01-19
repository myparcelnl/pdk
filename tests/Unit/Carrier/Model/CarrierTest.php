<?php
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('creates main carrier by name', function () {
    $carrier = new Carrier(['name' => Carrier::CARRIER_POSTNL_NAME]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_POSTNL_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_MAIN);
});

it('creates main carrier by carrierId', function () {
    $carrier = new Carrier(['id' => Carrier::CARRIER_INSTABOX_ID]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_INSTABOX_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_MAIN);
});

it('creates custom carrier by subscriptionId', function () {
    $carrier = new Carrier(['subscriptionId' => 10932621]);

    expect($carrier->getName())
        ->toBe(Carrier::CARRIER_DPD_NAME)
        ->and($carrier->getType())
        ->toBe(Carrier::TYPE_CUSTOM);
});

it('returns complete carrier object', function () {
    $carrier = new Carrier(['id' => Carrier::CARRIER_INSTABOX_ID]);

    expect(array_filter(Arr::dot($carrier->toArray()), function ($item) { return null !== $item; }))
        ->toEqual(
            [
                'id'                                                         => Carrier::CARRIER_INSTABOX_ID,
                'name'                                                       => Carrier::CARRIER_INSTABOX_NAME,
                'primary'                                                    => true,
                'isDefault'                                                  => false,
                'optional'                                                   => false,
                'type'                                                       => Carrier::TYPE_MAIN,
                'isDefault'                                                  => false,
                'optional'                                                   => false,
                'returnCapabilities'                                         => [],
                'capabilities.0.packageType.id'                              => 1,
                'capabilities.0.packageType.name'                            => 'package',
                'capabilities.0.deliveryTypes.0.id'                          => 2,
                'capabilities.0.deliveryTypes.0.name'                        => 'standard',
                'capabilities.0.shipmentOptions.labelDescription.type'       => 'string',
                'capabilities.0.shipmentOptions.labelDescription.minLength'  => 0,
                'capabilities.0.shipmentOptions.labelDescription.maxLength'  => 45,
                'capabilities.1.packageType.id'                              => 2,
                'capabilities.1.packageType.name'                            => 'mailbox',
                'capabilities.1.deliveryTypes'                               => [],
                'capabilities.1.shipmentOptions.labelDescription.type'       => 'string',
                'capabilities.1.shipmentOptions.labelDescription.minLength'  => 0,
                'capabilities.1.shipmentOptions.labelDescription.maxLength'  => 45,
                'capabilities.0.shipmentOptions.ageCheck.type'               => 'boolean',
                'capabilities.0.shipmentOptions.dropOffAtPostalPoint.type'   => 'boolean',
                'capabilities.0.shipmentOptions.dropOffAtPostalPoint.enum.0' => false,
                'capabilities.0.shipmentOptions.insurance.type'              => 'null',
                'capabilities.0.shipmentOptions.largeFormat.type'            => 'boolean',
                'capabilities.0.shipmentOptions.onlyRecipient.type'          => 'boolean',
                'capabilities.0.shipmentOptions.return.type'                 => 'boolean',
                'capabilities.0.shipmentOptions.sameDayDelivery.type'        => 'boolean',
                'capabilities.0.shipmentOptions.saturdayDelivery.type'       => 'boolean',
                'capabilities.0.shipmentOptions.saturdayDelivery.enum.0'     => false,
                'capabilities.0.shipmentOptions.signature.type'              => 'boolean',
                'capabilities.1.shipmentOptions.ageCheck.type'               => 'boolean',
                'capabilities.1.shipmentOptions.ageCheck.enum.0'             => false,
                'capabilities.1.shipmentOptions.dropOffAtPostalPoint.type'   => 'boolean',
                'capabilities.1.shipmentOptions.dropOffAtPostalPoint.enum.0' => false,
                'capabilities.1.shipmentOptions.insurance.type'              => 'null',
                'capabilities.1.shipmentOptions.largeFormat.type'            => 'boolean',
                'capabilities.1.shipmentOptions.largeFormat.enum.0'          => false,
                'capabilities.1.shipmentOptions.onlyRecipient.type'          => 'boolean',
                'capabilities.1.shipmentOptions.onlyRecipient.enum.0'        => false,
                'capabilities.1.shipmentOptions.return.type'                 => 'boolean',
                'capabilities.1.shipmentOptions.return.enum.0'               => false,
                'capabilities.1.shipmentOptions.sameDayDelivery.type'        => 'boolean',
                'capabilities.1.shipmentOptions.saturdayDelivery.type'       => 'boolean',
                'capabilities.1.shipmentOptions.saturdayDelivery.enum.0'     => false,
                'capabilities.1.shipmentOptions.signature.type'              => 'boolean',
            ]
        );
});

it('creates empty carrier and log', function () {
    $carrier = new Carrier(['name' => 'not_a_carrier']);

    expect($carrier->getName())
        ->toBe(null)
        ->and($carrier->getType())
        ->toBe(null)
        ->and(DefaultLogger::getLogs())
        ->toBe([
            [
                'level'   => 'warning',
                'message' => '[PDK]: Could not find a matching carrier',
                'context' => [
                    'input' => ['name' => 'not_a_carrier'],
                ],
            ],
        ]);
});

