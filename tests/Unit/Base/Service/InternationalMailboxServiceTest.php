<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\InternationalMailbox;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout');
usesShared(new UsesMockPdkInstance());

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
    //todo: maak een test voor elke mogelijke uitkomst
    // ja-ja
    // ja-nee
    // nee-ja
    // nee-nee

    //ja-ja
    $fakeCarrier = factory(Carrier::class)
        ->withExternalIdentifier('postnl:123')
        ->make();

    factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
        ->withAllowInternationalMailbox(true)
        ->withPriceInternationalMailbox(5)
        ->withDeliveryOptionsEnabled(true)
        ->withAllowDeliveryOptions(true)
        ->store();

    $result = InternationalMailbox::internationalMailboxPossible($fakeCarrier);
    expect($result)->toBeTrue();
});
