<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Frontend\Context;

use MyParcelNL\Pdk\App\Action\Frontend\Context\FetchCheckoutContextAction;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

beforeEach(function () {
    factory(Settings::class)->store();
    TestBootstrapper::hasAccount();
});

it('returns only the checkout context', function () {
    /** @var FetchCheckoutContextAction $action */
    $action = Pdk::get(FetchCheckoutContextAction::class);

    $response = $action->handle(new Request(['cart' => 'my-cart-id']));

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $body = json_decode($response->getContent(), true);

    expect($body['data'])
        ->toHaveKey('context')
        ->and($body['data']['context'])->toHaveCount(1)
        ->and(array_keys($body['data']['context'][0]))->toBe([Context::ID_CHECKOUT]);
});

it('does not leak the account api key, even when the caller requests another context', function () {
    /** @var FetchCheckoutContextAction $action */
    $action = Pdk::get(FetchCheckoutContextAction::class);

    // an anonymous caller must always receive the hard-coded checkout context
    $response = $action->handle(new Request(['cart' => 'my-cart-id', 'context' => Context::ID_DYNAMIC]));

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->not->toContain(TestBootstrapper::API_KEY_VALID)
        ->and(array_keys(json_decode($response->getContent(), true)['data']['context'][0]))
        ->toBe([Context::ID_CHECKOUT]);
});
