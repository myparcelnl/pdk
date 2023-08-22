<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderNoteRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesNotificationsMock(), new UsesSettingsMock());

it('updates pdk order note', function (
    bool   $barcodeInNote,
    string $barcodeInNoteTitle,
    string $externalIdentifier,
    array  $expectedOrderNote
) {
    /** @var MockPdkOrderNoteRepository $pdkOrderNoteRepository */
    $pdkOrderNoteRepository = Pdk::get(PdkOrderNoteRepositoryInterface::class);
    /** @var MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

    $settingsRepository->storeAllSettings(
        new Settings([
            GeneralSettings::ID => [
                GeneralSettings::BARCODE_IN_NOTE       => $barcodeInNote,
                GeneralSettings::BARCODE_IN_NOTE_TITLE => $barcodeInNoteTitle,
            ],
        ])
    );

    MockApi::enqueue(new Response(200, [], new ExamplePostShipmentsResponse()));

    $response = Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
        'orderIds' => ['123', '456'],
    ]);

    expect(
        $pdkOrderNoteRepository->getFromOrder($externalIdentifier)
            ->toArray()
    )->toBe($expectedOrderNote);
})->with(
    [[true, 'Barcode:', '123', ['title' => 'Barcode: 3STBJG1234567890']]]
);

