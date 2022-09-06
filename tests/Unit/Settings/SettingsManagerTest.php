<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Settings\Repository\ApiSettingsRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

beforeEach(function () {
    $this->pdk = PdkFactory::create(MockPdkConfig::create());
});

it('has correct default values', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
    $repository = $this->pdk->get(ApiSettingsRepository::class);
    $repository->set(new SettingsModel());

    $settings = Settings::all();

    expect($settings)
        ->toBeInstanceOf(SettingsModel::class)
        ->and(Arr::dot($settings->toArray()))
        ->toEqual([
            'carrier'                                    => [],
            'general.apiKey'                             => null,
            'general.barcodeInNote'                      => false,
            'general.conceptShipments'                   => true,
            'general.orderMode'                          => false,
            'general.processDirectly'                    => false,
            'general.shareCustomerInformation'           => false,
            'order.ignoreOrderStatuses'                  => null,
            'order.orderStatusMail'                      => null,
            'order.sendNotificationAfter'                => null,
            'order.sendOrderStateForDigitalStamps'       => null,
            'order.statusOnLabelCreate'                  => null,
            'order.statusWhenDelivered'                  => null,
            'order.statusWhenLabelScanned'               => null,
            'general.enableApiLogging'                   => false,
            'general.priceType'                          => null,
            'general.showDeliveryDay'                    => false,
            'general.trackTraceEmail'                    => false,
            'general.trackTraceMyAccount'                => false,
            'general.useSeparateAddressFields'           => false,
            'label.defaultPosition'                      => null,
            'label.labelDescription'                     => null,
            'label.labelOpenDownload'                    => null,
            'label.labelSize'                            => null,
            'label.promptPosition'                       => false,
            'customs.defaultCountryOfOrigin'             => null,
            'customs.defaultCustomsCode'                 => '0',
            'customs.defaultPackageContents'             => '1',
            'checkout.showPriceSurcharge'                => false,
            'checkout.pickupLocationsDefaultView'        => 'map',
            'checkout.strings.addressNotFound'           => null,
            'checkout.strings.cc'                        => null,
            'checkout.strings.city'                      => null,
            'checkout.strings.deliveryTitle'             => null,
            'checkout.strings.discount'                  => null,
            'checkout.strings.eveningDeliveryTitle'      => null,
            'checkout.strings.from'                      => null,
            'checkout.strings.houseNumber'               => null,
            'checkout.strings.loadMore'                  => null,
            'checkout.strings.morningDeliveryTitle'      => null,
            'checkout.strings.onlyRecipientTitle'        => null,
            'checkout.strings.openingHours'              => null,
            'checkout.strings.pickupLocationsListButton' => null,
            'checkout.strings.pickupLocationsMapButton'  => null,
            'checkout.strings.pickupTitle'               => null,
            'checkout.strings.postcode'                  => null,
            'checkout.strings.recipientTitle'            => null,
            'checkout.strings.retry'                     => null,
            'checkout.strings.saturdayDeliveryTitle'     => null,
            'checkout.strings.signatureTitle'            => null,
            'checkout.strings.standardDeliveryTitle'     => null,
            'checkout.strings.wrongNumberPostalCode'     => null,
            'checkout.strings.wrongPostalCodeCity'       => null,
        ]);
});

it('retrieves a single settings category', function () {
    /** @var \MyParcelNL\Pdk\Settings\Model\GeneralSettings $generalSettings */
    $generalSettings = Settings::get('general');

    expect($generalSettings)
        ->toBeInstanceOf(GeneralSettings::class)
        ->and(Arr::dot($generalSettings->toArray()))
        ->toHaveKeysAndValues([
            GeneralSettings::API_KEY => 'b03ad4237eab5bed119257012a4c5866',
        ]);
});

it('retrieves a specific setting', function () {
    $generalSettings = Settings::get('general.apiKey');

    expect($generalSettings)->toBe('b03ad4237eab5bed119257012a4c5866');
});

it('retrieves a specific setting via category', function () {
    /** @var GeneralSettings $generalSettings */
    $generalSettings = Settings::get('general');

    expect($generalSettings->apiKey)->toBe('b03ad4237eab5bed119257012a4c5866');
});

it('updates settings', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
    $repository = $this->pdk->get(ApiSettingsRepository::class);
    $settings   = Settings::all();

    $settings->general->apiKey = 'acd736d67a892fad08346738caf979bc';

    expect($repository->getFromStorage()->general->apiKey)->toEqual('b03ad4237eab5bed119257012a4c5866');
    Settings::persist();
    expect($repository->getFromStorage()->general->apiKey)->toEqual('acd736d67a892fad08346738caf979bc');
});

it('updates settings by category', function () {
    $settings = Settings::all();

    $settings->general->fill([
        'apiKey'           => 'e4277183fc0e2115d3d3d4bf54c17b76',
        'enableApiLogging' => true,
    ]);

    expect(Settings::get('general.apiKey'))
        ->toBe('e4277183fc0e2115d3d3d4bf54c17b76')
        ->and(Settings::get('general.enableApiLogging'))
        ->toBe(true);
});
