<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\AuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Facade\Audits;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets all audits', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAuditRepository $repository */
    $repository = Pdk::get(AuditRepositoryInterface::class);
    $audits     = $repository->all();

    expect($audits)->toBeInstanceOf(AuditCollection::class);
});

it('stores an audit', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAuditRepository $repository */
    $repository = Pdk::get(AuditRepositoryInterface::class);
    $audit      = factory(Audit::class)
        ->withId('123')
        ->make();

    Audits::add($audit);

    $audits = $repository->all();

    expect($audits)
        ->toHaveLength(1)
        ->and(
            $audits->first()
                ->getId()
        )
        ->toBe('123');
});
