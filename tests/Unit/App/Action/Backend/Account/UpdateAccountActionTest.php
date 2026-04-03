<?php

/** @noinspection NullPointerExceptionInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleAclResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

function executeUpdateAccount(
    ?array $settings,
    array  $accounts = null
): Response {
    MockApi::enqueue(
        new ExampleGetAccountsResponse($accounts),
        new ExampleAclResponse()
    );

    $request = new Request(
        [
            'action' => PdkBackendActions::UPDATE_ACCOUNT,
        ],
        [],
        [],
        [],
        [],
        [],
        $settings
            ? json_encode(['data' => ['account_settings' => $settings]])
            : null
    );

    return Actions::execute($request);
}

it('fetches account with shops', function () {
    /** @var MockPdkAccountRepository $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $accountRepository->deleteAccount();

    expect(AccountSettings::getAccount())->toBeNull();

    executeUpdateAccount([
        'apiKey' => 'test-api-key',
    ]);

    $account = AccountSettings::getAccount();

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->and($account->shops->all())
        ->toHaveLength(1);
});

it('fetches new account and carrier data from api when called with empty array', function () {
    $existingAccount = AccountSettings::getAccount();

    expect($existingAccount)->toBeInstanceOf(Account::class);

    executeUpdateAccount([]);

    $currentAccount = AccountSettings::getAccount();

    expect($currentAccount->toStorableArray())->toBe($existingAccount->toStorableArray());
});

it('updates validity of api key', function(?string $apiKey, bool $expectedValidity) {
    executeUpdateAccount(['apiKey' => $apiKey]);

    $account = Settings::all()->account;
    expect($account->apiKeyValid)->toBe($expectedValidity);
})->with([null, false], ['', false], ['valid-api-key', true]);
