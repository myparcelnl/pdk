<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;

/**
 * Sets up a default Account with Shop and Carriers for tests that need account data.
 * Leverages existing TestBootstrapper and factory infrastructure.
 *
 * Provides a default account with:
 * - Valid API key
 * - Single shop with minimal carrier capabilities
 * - MyParcel platform
 */
final class UsesAccountMock implements BaseMock
{
    /**
     * Reset the account to its default state before each test, so any mutations made by an
     * individual test (e.g. replacing carriers) do not leak into subsequent tests.
     */
    public function beforeEach(): void
    {
        TestBootstrapper::hasAccount();
    }

    /**
     * Clean up account data after each test.
     */
    public function afterEach(): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $accountRepository */
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);

        $accountRepository->deleteAccount();
    }
}
