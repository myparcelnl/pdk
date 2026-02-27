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

it('fetches account with carrier configurations and options', function () {
    /** @var MockPdkAccountRepository $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $accountRepository->deleteAccount();

    expect(AccountSettings::getAccount())->toBeNull();

    executeUpdateAccount([
        'apiKey' => 'test-api-key',
    ]);

    $account   = AccountSettings::getAccount();
    $firstShop = $account->shops->first();

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->and($account->shops->all())
        ->toHaveLength(1)
        ->and($firstShop->carriers->all())
        ->toHaveLength(9)
        ->and($firstShop->carrierConfigurations->all())
        ->toHaveLength(1);
});

it('fetches new account and carrier data from api when called with empty array', function () {
    $existingAccount = AccountSettings::getAccount();

    expect($existingAccount)->toBeInstanceOf(Account::class);

    executeUpdateAccount([]);

    $currentAccount = AccountSettings::getAccount();

    expect($currentAccount->toStorableArray())->toBe($existingAccount->toStorableArray());
});

it('maps carriers correctly', function () {
    executeUpdateAccount(['apiKey' => 'test-api-key']);

    $firstShop = AccountSettings::getAccount()->shops->first();

    $externalIdentifiers = $firstShop->carriers
        ->pluck('externalIdentifier')
        ->all();

    expect($externalIdentifiers)
        ->toBe([
            'CHEAP_CARGO',
            'BOL',
            'DHL_FOR_YOU',
            'POSTNL',
            'DHL_PARCEL_CONNECT',
            'DHL_EUROPLUS',
            'DHL_FOR_YOU:12424',
            'UPS_STANDARD',
            'UPS_EXPRESS_SAVER',
        ]);
});

// TODO: remove/replace
it('maps carriers correctly with custom postnl contract', function () {
    executeUpdateAccount(['apiKey' => 'test-api-key'], null, [
        // This is a lot like what the API returns in case of a PostNL contract.
        [
            'id'         => 8940,
            'label'      => 'absent_on_delivery_note_platform_1',
            'carrier_id' => 1,
            'carrier'    => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'    => 1,
            'optional'   => 1,
            'primary'    => 1,
            'type'       => 'main',
        ],
        [
            'id'          => 23991,
            'carrier_id'  => 1,
            'carrier'     => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'     => 1,
            'optional'    => 1,
            'primary'     => 0,
            'type'        => 'custom',
            'contract_id' => 8123,
        ],
        [
            'id'         => 11079,
            'label'      => 'postnl_physical_contract',
            'carrier_id' => 1,
            'carrier'    => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'    => 0,
            'optional'   => 1,
            'primary'    => 1,
            'type'       => 'main',
        ],
        [
            'id'         => 11088,
            'label'      => 'postnl_package_small_nl',
            'carrier_id' => 1,
            'carrier'    => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'    => 1,
            'optional'   => 0,
            'primary'    => 1,
            'type'       => 'main',
        ],
    ]);

    $firstShop = AccountSettings::getAccount()->shops->first();

    $externalIdentifiers = $firstShop->carriers
        ->pluck('externalIdentifier')
        ->all();

    // If multiple PostNL carriers are present, the custom contract should be used.
    expect($externalIdentifiers)->toBe(['POSTNL:23991']);
});

it('updates validity of api key', function (?string $apiKey, bool $expectedValidity) {
    executeUpdateAccount(['apiKey' => $apiKey]);

    $account = Settings::all()->account;
    expect($account->apiKeyValid)->toBe($expectedValidity);
})->with([null, false], ['', false], ['valid-api-key', true]);

it('maps carriers correctly with multiple non-contract postnl entries', function () {
    executeUpdateAccount(['apiKey' => 'test-api-key'], null, [
        [
            'id'         => 1,
            'label'      => null,
            'carrier_id' => 1,
            'carrier'    => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'    => 1,
            'optional'   => 1,
            'primary'    => 1,
            'type'       => 'main',
        ],
        [
            'id'         => 1,
            'label'      => 'postnl_package_small_nl',
            'carrier_id' => 1,
            'carrier'    => [
                'id'   => 1,
                'name' => 'POSTNL',
            ],
            'enabled'    => 1,
            'optional'   => 0,
            'primary'    => 1,
            'type'       => 'main',
        ],
        // Add a dhl for you carrier to make sure it's not removed.
        [
            'id'          => 12424,
            'carrier_id'  => 9,
            'carrier'     => [
                'id'   => 9,
                'name' => 'DHL_FOR_YOU',
            ],
            'enabled'     => 1,
            'optional'    => 1,
            'primary'     => 0,
            'type'        => 'custom',
            'contract_id' => 677,
        ],
    ]);

    $firstShop = AccountSettings::getAccount()->shops->first();

    $externalIdentifiers = $firstShop->carriers
        ->pluck('externalIdentifier')
        ->all();

    // If multiple PostNL carriers are present, but no custom contract, only the first one should be kept.
    expect($externalIdentifiers)->toBe(['DHL_FOR_YOU:12424', 'POSTNL']);
});
