<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Collection;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('filters by automatic type', function () {
    $auditCollection = factory(AuditCollection::class)
        ->push(
            factory(Audit::class)
                ->withModelIdentifier('123')
                ->withType(Audit::TYPE_MANUAL)
                ->withAction(PdkBackendActions::EXPORT_ORDERS),
            factory(Audit::class)
                ->withModelIdentifier('456')
                ->withType(Audit::TYPE_AUTOMATIC)
                ->withAction(PdkBackendActions::EXPORT_ORDERS)
        )
        ->make();

    $automaticAudits = $auditCollection->automatic();

    expect($automaticAudits)
        ->toHaveLength(1)
        ->and($automaticAudits->first()->modelIdentifier)
        ->toBe('456')
        ->and($automaticAudits->first()->type)
        ->toBe(Audit::TYPE_AUTOMATIC);
});

it('checks if action is present', function () {
    $auditCollection = factory(AuditCollection::class)
        ->push(
            factory(Audit::class)
                ->withModelIdentifier('123')
                ->withType(Audit::TYPE_MANUAL)
                ->withAction(PdkBackendActions::EXPORT_ORDERS),
            factory(Audit::class)
                ->withModelIdentifier('456')
                ->withType(Audit::TYPE_AUTOMATIC)
                ->withAction(PdkBackendActions::PRINT_ORDERS)
        )
        ->make();

    expect($auditCollection->hasAction(PdkBackendActions::EXPORT_ORDERS))
        ->toBeTrue()
        ->and($auditCollection->hasAction(PdkBackendActions::PRINT_ORDERS))
        ->toBeTrue()
        ->and($auditCollection->hasAction(PdkBackendActions::POST_ORDER_NOTES))
        ->toBeFalse();
});
