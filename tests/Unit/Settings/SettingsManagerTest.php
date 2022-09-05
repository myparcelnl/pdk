<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

beforeEach(function () {
    $this->pdk = PdkFactory::create(MockPdkConfig::create());
});

it('has correct default values', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
    $repository = $this->pdk->get(AbstractSettingsRepository::class);
    $repository->set(new SettingsModel());

    $settings = Settings::all();

    expect($settings)
        ->toBeInstanceOf(SettingsModel::class)
        ->and(Arr::dot($settings->toArray()))
        ->toEqual([
            'carrier'                                  => [],
            'checkout.deliveryOptionsCustomCss'        => null,
            'checkout.deliveryOptionsDisplay'          => false,
            'checkout.deliveryOptionsPosition'         => null,
            'checkout.pickupLocationsDefaultView'      => CheckoutSettings::DEFAULT_PICKUP_LOCATIONS_VIEW,
            'checkout.priceType'                       => null,
            'checkout.showDeliveryDay'                 => true,
            'checkout.showPriceAsSurcharge'            => false,
            'checkout.stringAddressNotFound'           => null,
            'checkout.stringCity'                      => null,
            'checkout.stringCountry'                   => null,
            'checkout.stringDelivery'                  => null,
            'checkout.stringDiscount'                  => null,
            'checkout.stringEveningDelivery'           => null,
            'checkout.stringFrom'                      => null,
            'checkout.stringHouseNumber'               => null,
            'checkout.stringLoadMore'                  => null,
            'checkout.stringMorningDelivery'           => null,
            'checkout.stringOnlyRecipient'             => null,
            'checkout.stringOpeningHours'              => null,
            'checkout.stringPickup'                    => null,
            'checkout.stringPickupLocationsListButton' => null,
            'checkout.stringPickupLocationsMapButton'  => null,
            'checkout.stringPostalCode'                => null,
            'checkout.stringRecipient'                 => null,
            'checkout.stringRetry'                     => null,
            'checkout.stringSaturdayDelivery'          => null,
            'checkout.stringSignature'                 => null,
            'checkout.stringStandardDelivery'          => null,
            'checkout.stringWrongNumberPostalCode'     => null,
            'checkout.stringWrongPostalCodeCity'       => null,
            'checkout.useSeparateAddressFields'        => false,
            'customs.countryOfOrigin'                  => null,
            'customs.customsCode'                      => CustomsSettings::DEFAULT_CUSTOMS_CODE,
            'customs.packageContents'                  => CustomsSettings::DEFAULT_PACKAGE_CONTENTS,
            'general.apiKey'                           => null,
            'general.apiLogging'                       => false,
            'general.barcodeInNote'                    => false,
            'general.conceptShipments'                 => true,
            'general.exportWithAutomaticStatus'        => null,
            'general.orderMode'                        => false,
            'general.processDirectly'                  => false,
            'general.shareCustomerInformation'         => false,
            'general.trackTraceInAccount'              => false,
            'general.trackTraceInEmail'                => false,
            'label.description'                        => null,
            'label.format'                             => LabelSettings::DEFAULT_FORMAT,
            'label.output'                             => LabelSettings::DEFAULT_OUTPUT,
            'label.position'                           => LabelSettings::DEFAULT_POSITION,
            'label.prompt'                             => false,
            'order.emptyDigitalStampWeight'            => null,
            'order.emptyParcelWeight'                  => null,
            'order.ignoreOrderStatuses'                => null,
            'order.orderStatusMail'                    => true,
            'order.saveCustomerAddress'                => false,
            'order.sendNotificationAfter'              => null,
            'order.sendOrderStateForDigitalStamp'      => true,
            'order.statusOnLabelCreate'                => null,
            'order.statusWhenDelivered'                => null,
            'order.statusWhenLabelScanned'             => null,
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
    $repository = $this->pdk->get(AbstractSettingsRepository::class);
    $settings   = Settings::all();

    $settings->general->apiKey = 'acd736d67a892fad08346738caf979bc';

    expect($repository->getFromStorage()->general->apiKey)->toEqual('b03ad4237eab5bed119257012a4c5866');
    Settings::persist();
    expect($repository->getFromStorage()->general->apiKey)->toEqual('acd736d67a892fad08346738caf979bc');
});

it('updates settings by category', function () {
    $settings = Settings::all();

    $settings->general->fill([
        GeneralSettings::API_KEY     => 'e4277183fc0e2115d3d3d4bf54c17b76',
        GeneralSettings::API_LOGGING => true,
    ]);

    expect(Settings::get('general.apiKey'))
        ->toBe('e4277183fc0e2115d3d3d4bf54c17b76')
        ->and(Settings::get('general.apiLogging'))
        ->toBe(true);
});
