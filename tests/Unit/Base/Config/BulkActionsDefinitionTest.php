<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Config;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function MyParcelNL\Pdk\Tests\factory;

it('gets bulk actions for the active order mode', function (array $features, array $actions) {
    MockPdkFactory::create();

    if (! empty($features)) {
        factory(Account::class)
            ->withSubscriptionFeatures($features)
            ->store();
    }

    /** @var array $bulkActions */
    $bulkActions = Pdk::get('bulkActions');

    expect($bulkActions)
        ->toBeArray()
        ->and($bulkActions)
        ->toBe($actions);
})
    ->with([
        'shipments mode' => [
            'features' => [],
            'actions'  => [
                'action_print',
                'action_export_print',
                'action_export',
                'action_edit',
            ],
        ],

        'order v1 mode' => [
            'features' => [PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT],
            'actions'  => [
                'action_edit',
                'action_export',
            ],
        ],

        'order v2 mode (hybrid: shipment-mode actions remain available)' => [
            'features' => [PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT],
            'actions'  => [
                'action_print',
                'action_export_print',
                'action_export',
                'action_edit',
            ],
        ],
    ]);
