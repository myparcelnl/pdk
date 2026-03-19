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
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleContractDefinitionsResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesSdkApiMock());

function executeUpdateAccount(
    ?array $settings,
    array  $accounts = null
): Response {
    MockSdkApiHandler::enqueue(
        new ExampleContractDefinitionsResponse()
    );
    MockApi::enqueue(
        new ExampleGetAccountsResponse($accounts),
        new ExampleAclResponse()
    );

    // Call the actual update account endpoint (uses the mocked responses above)
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

it('fetches account with shops and carrier capabilities', function () {
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
        ->toHaveLength(5);
});

it('fetches new account and carrier data from api when called with empty array', function () {
    $existingAccount = AccountSettings::getAccount();

    expect($existingAccount)->toBeInstanceOf(Account::class);

    executeUpdateAccount([]);

    $currentAccount = AccountSettings::getAccount();

    expect($currentAccount->toStorableArray())->toBe($existingAccount->toStorableArray());
});

it('saves carrier capabilities as account->shop->carriers correctly', function () {
    executeUpdateAccount(['apiKey' => 'test-api-key']);

    $firstShop = AccountSettings::getAccount()->shops->first();

    $carrierNames = $firstShop->carriers
        ->pluck('carrier')
        ->all();

    // Check the presence of carriers from the mocked contract definitions response
    expect($carrierNames)
        ->toContain('POSTNL')
        ->toContain('DPD')
        ->toContain('DHL_FOR_YOU')
        ->toContain('DHL_PARCEL_CONNECT')
        ->toContain('DHL_EUROPLUS');

    // Check all capabilities for POSTNL from the mocked contract definitions response
    $firstCarrier = $firstShop->carriers->firstWhere('carrier', 'POSTNL');

    expect($firstCarrier->packageTypes)
        ->toContain('PACKAGE')
        ->toContain('MAILBOX')
        ->toContain('UNFRANKED')
        ->toContain('DIGITAL_STAMP')
        ->toContain('SMALL_PACKAGE');

    expect($firstCarrier->deliveryTypes)
        ->toContain('STANDARD_DELIVERY')
        ->toContain('MORNING_DELIVERY')
        ->toContain('EVENING_DELIVERY')
        ->toContain('PICKUP_DELIVERY');

    expect($firstCarrier->transactionTypes)
        ->toContain('B2C')
        ->toContain('B2B');

    $options = $firstCarrier->options;
    expect($options)->toBeInstanceOf(RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::class);

    expect($options->getRequiresAgeVerification()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getRequiresAgeVerification()->getIsRequired())->toBeFalse();

    expect($options->getRequiresSignature()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getRequiresSignature()->getIsRequired())->toBeFalse();

    expect($options->getRequiresReceiptCode()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getRequiresReceiptCode()->getIsRequired())->toBeFalse();

    expect($options->getOversizedPackage()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getOversizedPackage()->getIsRequired())->toBeFalse();

    expect($options->getRecipientOnlyDelivery()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getRecipientOnlyDelivery()->getIsRequired())->toBeFalse();

    expect($options->getPrintReturnLabelAtDropOff()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getPrintReturnLabelAtDropOff()->getIsRequired())->toBeFalse();

    expect($options->getPriorityDelivery()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getPriorityDelivery()->getIsRequired())->toBeFalse();

    expect($options->getReturnOnFirstFailedDelivery()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getReturnOnFirstFailedDelivery()->getIsRequired())->toBeFalse();

    expect($options->getNoTracking()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getNoTracking()->getIsRequired())->toBeFalse();

    // @TODO: tracked is currently dropped during SDK deserialization because it is missing from the attributeMap
    // of RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2 — fix the SDK model and this assertion should pass
    expect($options->getTracked()->getIsSelectedByDefault())->toBeFalse();
    expect($options->getTracked()->getIsRequired())->toBeFalse();

    $insurance = $options->getInsurance();
    expect($insurance->getIsSelectedByDefault())->toBeFalse();
    expect($insurance->getIsRequired())->toBeFalse();
    expect($insurance->getInsuredAmount()->getDefault()->getCurrency())->toBe('EUR');
    expect($insurance->getInsuredAmount()->getDefault()->getAmount())->toBe(0);
    expect($insurance->getInsuredAmount()->getMin()->getCurrency())->toBe('EUR');
    expect($insurance->getInsuredAmount()->getMin()->getAmount())->toBe(0);
    expect($insurance->getInsuredAmount()->getMax()->getCurrency())->toBe('EUR');
    expect($insurance->getInsuredAmount()->getMax()->getAmount())->toBe(500000);

    expect($firstCarrier->collo)->toBe(['max' => 10]);
});

it('updates validity of api key', function (?string $apiKey, bool $expectedValidity) {
    executeUpdateAccount(['apiKey' => $apiKey]);

    $account = Settings::all()->account;
    expect($account->apiKeyValid)->toBe($expectedValidity);
})->with([null, false], ['', false], ['valid-api-key', true]);
