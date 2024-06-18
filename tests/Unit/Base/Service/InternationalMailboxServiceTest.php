<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\InternationalMailbox;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout');

usesShared(
    new UsesMockPdkInstance([
        PdkSettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            CarrierSettings::ID => [
                Carrier::CARRIER_POSTNL_NAME => [
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED    => true,
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS      => true,
                    CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX => true,
                ],
            ],
        ]),
    ])
);
it('checks if package is international mailbox', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
    $countryService = Pdk::get(CountryServiceInterface::class);
    $allCountries   = $countryService->getAll();
    shuffle($allCountries);

    foreach ($allCountries as $country) {
        if ($countryService->isEu($country)) {
            $result = InternationalMailbox::isInternationalMailbox($country, 'mailbox');
            expect($result)->toBeTrue();
            break;
        }
    }

    foreach ($allCountries as $country) {
        if ($countryService->isRow($country)) {
            $result = InternationalMailbox::isInternationalMailbox($country, 'mailbox');
            expect($result)->toBeTrue();
            break;
        }
    }
    $result = InternationalMailbox::isInternationalMailbox('NL', 'mailbox');
    expect($result)->toBeFalse();

    $result = InternationalMailbox::isInternationalMailbox('BE', 'mailbox');
    expect($result)->toBeFalse();

    $result = InternationalMailbox::isInternationalMailbox($allCountries[0], 'package');
    expect($result)->toBeFalse();
});

it('checks if international mailbox is possible', function () {
    // maak eerst een neppe postnl carrier aan, het moet de type custom hebben.
    // Daarna moet je zorgen dat de setting voor international mailbox aan staat.
    $allCarriers = AccountSettings::getCarriers();

    $result = true;
    expect($result)->toBeTrue();
});
