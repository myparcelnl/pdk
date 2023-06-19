<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Action\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('settings');

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

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

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
            ProductSettings::EXPORT_AGE_CHECK => true,
        ],
    ],

]);
