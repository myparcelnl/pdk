<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('saves settings', function (string $productId, array $settings) {
    $content = json_encode([
        'data' => [
            'product_settings' => $settings,
        ],
    ]);

    $request = new Request(
        [
            'action'    => PdkBackendActions::UPDATE_PRODUCT_SETTINGS,
            'productId' => $productId,
        ],
        [],
        [],
        [],
        [],
        [],
        $content
    );

    $response = Actions::execute($request);

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200);

    assertMatchesJsonSnapshot(json_encode($content));
})->with([
    'enable export age check for 123' => [
        '123',
        [
            ProductSettings::EXPORT_AGE_CHECK => 1,
        ],
    ],

    'changes all options for 789' => [
        '789',
        [
            ProductSettings::EXPORT_ONLY_RECIPIENT    => 1,
            ProductSettings::EXPORT_SIGNATURE         => 1,
            ProductSettings::COUNTRY_OF_ORIGIN        => 'DE',
            ProductSettings::CUSTOMS_CODE             => '388',
            ProductSettings::DISABLE_DELIVERY_OPTIONS => 1,
            ProductSettings::DROP_OFF_DELAY           => 2,
            ProductSettings::EXPORT_AGE_CHECK         => 1,
            ProductSettings::EXPORT_INSURANCE         => 1,
            ProductSettings::EXPORT_LARGE_FORMAT      => 1,
            ProductSettings::FIT_IN_MAILBOX           => 10,
            ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ProductSettings::EXPORT_RETURN            => 1,
        ],
    ],
]);
