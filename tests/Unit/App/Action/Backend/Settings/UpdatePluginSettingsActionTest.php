<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('saves settings', function (array $data) {
    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'order' => [
                    'order_mode' => $data['order_mode'],
                ],
            ],
        ],
    ]);
    $request = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);

    $response = Actions::execute($request);

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.plugin_settings.order.orderMode' => $data['order_mode'],
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
})->with([
    [['order_mode' => false]],
    [['order_mode' => true]],
]);

it('resets delivery options when disabled', function () {
    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'carrier' => [
                    'postnl' => [
                        'deliveryOptionsEnabled'   => false,
                        'allowDeliveryOptions'     => true,
                        'allowStandardDelivery'    => true,
                        'allowMorningDelivery'     => true,
                        'allowEveningDelivery'     => true,
                        'allowSameDayDelivery'     => true,
                        'allowMondayDelivery'      => true,
                        'allowSaturdayDelivery'    => true,
                        'allowSignature'           => true,
                        'allowOnlyRecipient'       => true,
                        'allowPriorityDelivery'    => true,
                        'allowPickupLocations'     => true,
                        'allowDeliveryTypeExpress' => true,
                    ],
                ],
            ],
        ],
    ]);
    $request = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);

    $response = Actions::execute($request);

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($content['data']['plugin_settings']['carrier']['postnl'])
        ->toHaveKeysAndValues([
            'deliveryOptionsEnabled'   => false,
            'allowDeliveryOptions'     => false,
            'allowStandardDelivery'    => false,
            'allowMorningDelivery'     => false,
            'allowEveningDelivery'     => false,
            'allowSameDayDelivery'     => false,
            'allowMondayDelivery'      => false,
            'allowSaturdayDelivery'    => false,
            'allowSignature'           => false,
            'allowOnlyRecipient'       => false,
            'allowPriorityDelivery'    => false,
            'allowPickupLocations'     => false,
            'allowDeliveryTypeExpress' => false,
        ]);
});

it('normalizes carrier-specific shipment option dependencies when saving carrier settings', function (
    string $carrierName,
    array $carrierSettings,
    array $expected
) {
    factory(Carrier::class)
        ->withAllCapabilities($carrierName)
        ->store();

    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'carrier' => [
                    $carrierName => array_merge(
                        ['id' => $carrierName],
                        $carrierSettings
                    ),
                ],
            ],
        ],
    ]);

    $request  = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);
    $response = Actions::execute($request);
    $content  = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($content['data']['plugin_settings']['carrier'][$carrierName])
        ->toHaveKeysAndValues($expected);
})->with([
    'postnl: age check also enables signature and only recipient' => [
        RefCapabilitiesSharedCarrierV2::POSTNL,
        [
            'exportAgeCheck'      => 1,
            'exportSignature'     => 0,
            'exportOnlyRecipient' => 0,
        ],
        [
            'exportAgeCheck'      => 1,
            'exportSignature'     => 1,
            'exportOnlyRecipient' => 1,
        ],
    ],
    'ups standard: age check enables signature only' => [
        RefCapabilitiesSharedCarrierV2::UPS_STANDARD,
        [
            'exportAgeCheck'      => 1,
            'exportSignature'     => 0,
            'exportOnlyRecipient' => 0,
        ],
        [
            'exportAgeCheck'      => 1,
            'exportSignature'     => 1,
            'exportOnlyRecipient' => 0,
        ],
    ],
    'dhl for you: age check disables only recipient' => [
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU,
        [
            'exportAgeCheck'      => 1,
            'exportOnlyRecipient' => 1,
        ],
        [
            'exportAgeCheck'      => 1,
            'exportOnlyRecipient' => 0,
        ],
    ],
]);

it('normalizes carrier settings when the payload uses carrier collection keys as identifiers', function () {
    factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->store();

    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'carrier' => [
                    'id'     => 'carrier',
                    'POSTNL' => [
                        'id'                  => 'carrier',
                        'exportAgeCheck'      => 1,
                        'exportSignature'     => 0,
                        'exportOnlyRecipient' => 0,
                    ],
                ],
            ],
        ],
    ]);

    $request  = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);
    $response = Actions::execute($request);
    $content  = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($content['data']['plugin_settings']['carrier']['POSTNL'])
        ->toHaveKeysAndValues([
            'exportAgeCheck'      => 1,
            'exportSignature'     => 1,
            'exportOnlyRecipient' => 1,
        ]);
});
