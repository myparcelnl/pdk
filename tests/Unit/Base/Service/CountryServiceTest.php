<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('returns all languages', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = $pdk->get(CountryService::class);
    $all     = $service->getAll();

    expect($all)
        ->not->toBeEmpty()
        ->and($all)
        ->toBeArray();
});
