<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

// Site 2: proposition.defaultCarrier is present when shop has a default carrier set.
it('includes the V2 default carrier name in the proposition payload when shop has a default', function () {
    // UsesAccountMock sets up a shop with all carriers. Mutate its defaultCarrier.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $repo->store($account);

    $context = new GlobalContext();

    expect($context->proposition['defaultCarrier'])->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

// Site 2: proposition.defaultCarrier is null when shop has no default carrier set.
it('sets proposition.defaultCarrier to null when shop has no default', function () {
    // Explicitly clear the defaultCarrier that ShopFactory sets by default.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = null;
    $repo->store($account);

    $context = new GlobalContext();

    expect($context->proposition['defaultCarrier'])->toBeNull();
});
