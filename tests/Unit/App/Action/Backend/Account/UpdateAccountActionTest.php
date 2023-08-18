<?php
/** @noinspection NullPointerExceptionInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetCarrierConfigurationResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetCarrierOptionsResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function executeUpdateAccount(?array $settings, array $accounts = null): Response
{
    MockApi::enqueue(
        new ExampleGetAccountsResponse($accounts),
        new ExampleGetCarrierConfigurationResponse(),
        new ExampleGetCarrierOptionsResponse()
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
    /** @var \MyParcelNL\Pdk\App\Account\Repository\MockPdkAccountRepository $accountRepository */
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
        ->toHaveLength(1)
        ->and($firstShop->carrierConfigurations->all())
        ->toHaveLength(1);
});
