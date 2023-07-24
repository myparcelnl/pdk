<?php
/** @noinspection NullPointerExceptionInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleAclResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

it('fetches account with carrier configurations and options', function () {
    MockApi::enqueue(new ExampleAclResponse());

    $response = Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);
    $content  = json_decode($response->getContent(), true);

    expect($response)
        ->not()
        ->toBeNull()
        ->and($content)
        ->toBeArray();
});
