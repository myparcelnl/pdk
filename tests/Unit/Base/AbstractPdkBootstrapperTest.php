<?php

/** @noinspection AutoloadingIssuesInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;

use function DI\value;
use function MyParcelNL\Pdk\Tests\factory;

afterAll(function () {
    Bootstrapper::reset();
});

class Bootstrapper extends PdkBootstrapper
{
    public static function reset(): void
    {
        self::$initialized = false;
        self::$pdk         = null;
        PdkFacade::setPdkInstance(null);
    }

    protected function getAdditionalConfig(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): array {
        return [
            'arbitraryValue' => value('arbitraryValue'),

            'appInfoArray' => [
                'name'    => $name,
                'title'   => $title,
                'version' => $version,
                'path'    => $path,
                'url'     => $url,
            ],
        ];
    }
}

it('can boot the PDK with app info', function () {
    $appInfoInput = [
        'version' => '1.0.0',
        'path'    => __DIR__ . '/../../..',
        'url'     => 'https://example.com',
    ];

    $pdk = Bootstrapper::boot(...array_values($appInfoInput));

    expect($pdk)->toBeInstanceOf(Pdk::class);

    $appInfo = $pdk->getAppInfo();

    expect($appInfo)
        ->toBeInstanceOf(AppInfo::class)
        ->and($appInfo->toArray())
        ->toMatchArray($appInfoInput);
});

it('only boots the instance once', function () {
    Bootstrapper::reset();
    Bootstrapper::boot('1.0.0', __DIR__ . '/../../..', 'https://example.com');
    // Not resetting the bootstrapper here, so the second boot should return the same instance.
    $pdk = Bootstrapper::boot('9.9.9', __DIR__ . '/../../..', 'https://example.com');

    expect($pdk->getAppInfo()->version)->toBe('1.0.0');
});

it('can boot the PDK with additional config', function () {
    Bootstrapper::reset();

    $appInfoInput = [
        'version' => '1.2.3',
        'path'    => '/path/to/app',
        'url'     => 'https://example.com',
    ];

    $pdk = Bootstrapper::boot(...array_values($appInfoInput));

    expect($pdk->get('arbitraryValue'))
        ->toBe('arbitraryValue')
        ->and($pdk->get('appInfoArray'))
        ->toMatchArray($appInfoInput);
});

it('determines proposition from account', function (int $platformId, string $platform) {
    TestBootstrapper::forPlatform($platform);
    $propositionService = PdkFacade::get(PropositionService::class);

    factory(Account::class, $platformId)
        ->withShops()
        ->store();

    expect($propositionService->getActivePropositionId())
        ->toBe($platformId)
        ->and($propositionService->getPropositionConfig()->proposition->key)
        ->toBe($platform);
})->with([
    'myparcelnl'          => [Platform::MYPARCEL_ID, Platform::MYPARCEL_NAME],
    'myparcelbe'          => [Platform::SENDMYPARCEL_ID, Platform::SENDMYPARCEL_NAME],
]);
