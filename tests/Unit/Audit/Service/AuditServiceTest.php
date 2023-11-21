<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Service;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAuditClass;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets all audits', function () {
    $auditsFactory = factory(AuditCollection::class)
        ->push(
            factory(Audit::class)
                ->withModelIdentifier('foo'),
            factory(Audit::class)
                ->withModelIdentifier('bar')
        );

    $auditsFactory->store();

    $auditService = Pdk::get(AuditServiceInterface::class);
    $audits       = $auditService->all();

    expect($audits)
        ->toBeInstanceOf(AuditCollection::class)
        ->and($audits->count())
        ->toBe(2);
});

it('adds audits', function () {
    $auditService = Pdk::get(AuditServiceInterface::class);

    /** @var \MyParcelNL\Pdk\Audit\Model\Audit $audit */
    $audit = $auditService->add(
        factory(Audit::class)
            ->withModelIdentifier('foo')
            ->make()
    );

    expect($audit)
        ->toBeInstanceOf(Audit::class)
        ->and($audit->modelIdentifier)
        ->toBe('foo');
});

it('gets audits for a given model', function ($model, $modelIdentifier) {
    factory(AuditCollection::class)
        ->push(
            factory(Audit::class)
                ->withModelIdentifier('randomIdentifier')
                ->withModel(MockAuditClass::class),
            factory(Audit::class)
                ->withModelIdentifier('externalIdentifier')
                ->withModel(PdkOrder::class)
        )
        ->store();

    $auditService = Pdk::get(AuditServiceInterface::class);
    $audits       = $auditService->allByModel($model, $modelIdentifier);

    expect($audits->first()->model)
        ->toBe($model)
        ->and($audits->first()->modelIdentifier)
        ->toBe($modelIdentifier);
})->with([
    [
        'model'           => PdkOrder::class,
        'modelIdentifier' => 'externalIdentifier',
    ],
    [
        'model'           => MockAuditClass::class,
        'modelIdentifier' => 'randomIdentifier',
    ],
]);
