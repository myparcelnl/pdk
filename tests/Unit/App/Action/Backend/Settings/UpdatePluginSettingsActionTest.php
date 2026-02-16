<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

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
