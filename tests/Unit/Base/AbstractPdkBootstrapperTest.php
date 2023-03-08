<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Base;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use function DI\value;

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
        'name'    => 'myparcelnl-test',
        'title'   => 'MyParcel',
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
        ->toEqual($appInfoInput);
});

it('only boots the instance once', function () {
    // Not resetting the bootstrapper here, so the second boot should return the same instance.
    $pdk = Bootstrapper::boot('other-name', 'MyParcel', '1.0.0', __DIR__ . '/../../..', 'https://example.com');

    expect($pdk->getAppInfo()->name)->toBe('myparcelnl-test');
});

it('determines platform automatically', function (string $name, string $platform) {
    Bootstrapper::reset();

    $pdk = Bootstrapper::boot($name, 'MyParcel', '1.0.0', __DIR__ . '/../../..', 'https://example.com');

    expect($pdk->get('platform'))->toBe($platform);
})->with([
    'myparcelnl'          => ['myparcelnl', Platform::MYPARCEL_NAME],
    'myparcelbe'          => ['myparcelbe', Platform::SENDMYPARCEL_NAME],
    'flespakket'          => ['flespakket', Platform::FLESPAKKET_NAME],
    'unrecognized string' => ['bla', Platform::MYPARCEL_NAME],
]);

it('can boot the PDK with additional config', function () {
    Bootstrapper::reset();

    $appInfoInput = [
        'name'    => 'app-name',
        'title'   => 'MyApp',
        'version' => '1.2.3',
        'path'    => '/path/to/app',
        'url'     => 'https://example.com',
    ];

    $pdk = Bootstrapper::boot(...array_values($appInfoInput));

    expect($pdk->get('arbitraryValue'))
        ->toBe('arbitraryValue')
        ->and($pdk->get('appInfoArray'))
        ->toEqual($appInfoInput);
});
