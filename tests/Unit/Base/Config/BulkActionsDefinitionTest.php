<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Config;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function MyParcelNL\Pdk\Tests\factory;

it('gets bulk actions', function (bool $orderMode, array $actions) {
    MockPdkFactory::create();

    if ($orderMode) {
        factory(Account::class)
            ->withSubscriptionFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT])
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
        'default' => [
            'order mode' => false,
            'default'    => [
                'action_print',
                'action_export_print',
                'action_export',
                'action_edit',
            ],
        ],

        'order mode' => [
            'order mode' => true,
            'actions'    => [
                'action_edit',
                'action_export',
            ],
        ],
    ]);
