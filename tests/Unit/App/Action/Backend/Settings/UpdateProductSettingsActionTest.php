<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('saves settings', function (string $productId, array $settings, array $newSettings = null) {
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

    expect($response->getStatusCode())
        ->toBe(200)
        ->and(Arr::get($content, 'data.product_settings'))
        ->toEqual(
            array_replace([
                'id'                                      => ProductSettings::ID,
                ProductSettings::COUNTRY_OF_ORIGIN        => TriStateService::INHERIT,
                ProductSettings::CUSTOMS_CODE             => TriStateService::INHERIT,
                ProductSettings::DISABLE_DELIVERY_OPTIONS => TriStateService::INHERIT,
                ProductSettings::DROP_OFF_DELAY           => TriStateService::INHERIT,
                ProductSettings::EXPORT_AGE_CHECK         => TriStateService::INHERIT,
                ProductSettings::EXPORT_HIDE_SENDER       => TriStateService::INHERIT,
                ProductSettings::EXPORT_INSURANCE         => TriStateService::INHERIT,
                ProductSettings::EXPORT_LARGE_FORMAT      => TriStateService::INHERIT,
                ProductSettings::EXPORT_ONLY_RECIPIENT    => TriStateService::INHERIT,
                ProductSettings::EXPORT_RETURN            => TriStateService::INHERIT,
                ProductSettings::EXPORT_SIGNATURE         => TriStateService::INHERIT,
                ProductSettings::FIT_IN_DIGITAL_STAMP     => TriStateService::INHERIT,
                ProductSettings::FIT_IN_MAILBOX           => TriStateService::INHERIT,
                ProductSettings::PACKAGE_TYPE             => TriStateService::INHERIT,
            ], $newSettings ?? $settings)
        );
})->with([
    'keeps default options'       => ['123', []],
    'changes all options for 789' => [
        '789',
        [
            ProductSettings::COUNTRY_OF_ORIGIN        => 'DE',
            ProductSettings::CUSTOMS_CODE             => '388',
            ProductSettings::DISABLE_DELIVERY_OPTIONS => TriStateService::ENABLED,
            ProductSettings::DROP_OFF_DELAY           => 2,
            ProductSettings::EXPORT_AGE_CHECK         => TriStateService::ENABLED,
            ProductSettings::EXPORT_HIDE_SENDER       => TriStateService::ENABLED,
            ProductSettings::EXPORT_INSURANCE         => TriStateService::ENABLED,
            ProductSettings::EXPORT_LARGE_FORMAT      => TriStateService::ENABLED,
            ProductSettings::EXPORT_ONLY_RECIPIENT    => TriStateService::ENABLED,
            ProductSettings::EXPORT_RETURN            => TriStateService::ENABLED,
            ProductSettings::EXPORT_SIGNATURE         => TriStateService::ENABLED,
            ProductSettings::FIT_IN_DIGITAL_STAMP     => TriStateService::ENABLED,
            ProductSettings::FIT_IN_MAILBOX           => 10,
            ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        ],
    ],
]);
