<?php
/** @noinspection AutoloadingIssuesInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Audit\Concern;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\AuditRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAuditClass;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('audits');
usesShared(new UsesMockPdkInstance());

it('gets all audits for a model', function () {
    $mockAuditClass = new MockAuditClass(['randomIdentifier' => '1']);

    $emptyAudits = $mockAuditClass->audits->all();

    expect($emptyAudits)->toBeEmpty();

    $mockAuditClass->addAudit(PdkBackendActions::EXPORT_ORDERS);

    $auditRepository = Pdk::get(AuditRepositoryInterface::class);
    $audits          = $auditRepository->all();

    expect($audits)
        ->toHaveLength(1)
        ->and($audits->first()->action)
        ->toBe(PdkBackendActions::EXPORT_ORDERS);
});

it('initializes class with audits property', function () {
    $mockAuditClass = new MockAuditClass(['randomIdentifier' => '1']);

    expect($mockAuditClass->audits)
        ->toBeInstanceOf(AuditCollection::class);
});
