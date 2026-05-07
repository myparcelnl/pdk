<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
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

    $content  = json_decode($response->getContent(), true);
    $expected = (new ProductSettings($newSettings ?? $settings))->toArrayWithoutNull();

    expect($response->getStatusCode())
        ->toBe(200)
        ->and(Arr::get($content, 'data.product_settings'))
        ->toEqual($expected);
})->with([
    'keeps default options'       => ['123', []],
    'changes all options for 789' => [
        '789',
        [
            ProductSettings::COUNTRY_OF_ORIGIN        => 'DE',
            ProductSettings::CUSTOMS_CODE             => '388',
            ProductSettings::DISABLE_DELIVERY_OPTIONS => TriStateService::ENABLED,
            ProductSettings::DROP_OFF_DELAY           => 2,
            (new AgeCheckDefinition())->getProductSettingsKey()      => TriStateService::ENABLED,
            (new HideSenderDefinition())->getProductSettingsKey()    => TriStateService::ENABLED,
            (new InsuranceDefinition())->getProductSettingsKey()     => TriStateService::ENABLED,
            (new LargeFormatDefinition())->getProductSettingsKey()   => TriStateService::ENABLED,
            (new OnlyRecipientDefinition())->getProductSettingsKey() => TriStateService::ENABLED,
            (new DirectReturnDefinition())->getProductSettingsKey()  => TriStateService::ENABLED,
            (new SignatureDefinition())->getProductSettingsKey()     => TriStateService::ENABLED,
            (new TrackedDefinition())->getProductSettingsKey()       => TriStateService::ENABLED,
            ProductSettings::FIT_IN_DIGITAL_STAMP     => TriStateService::ENABLED,
            ProductSettings::FIT_IN_MAILBOX           => 10,
            ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        ],
    ],
]);
