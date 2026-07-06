<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Service;

use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\Api\PdkCapabilitiesActions;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\App\Action\Frontend\Context\FetchCheckoutContextAction;
use MyParcelNL\Pdk\App\Action\Shared\Context\FetchContextAction;
use MyParcelNL\Pdk\App\Api\Frontend\PdkFrontendActions;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Resolve the action class PdkActionsService would run for a given endpoint context + action name.
 * Exercises the real (private) resolveAction() against the real config/actions.php map, without
 * executing the action itself.
 */
if (! function_exists(__NAMESPACE__ . '\\resolveEndpointAction')) {
    function resolveEndpointAction(string $context, string $action): string
    {
        $service = (new PdkActionsService())->setContext($context);

        $method = new ReflectionMethod(PdkActionsService::class, 'resolveAction');
        $method->setAccessible(true);

        // context=dynamic mirrors the disclosure attempt; resolveAction only reads "action".
        return $method->invoke($service, new Request(['action' => $action, 'context' => 'dynamic']));
    }
}

/**
 * Regression test, `fetchContext` must be resolvable from the backend context only.
 */
it('does NOT expose fetchContext to the unauthenticated frontend endpoint', function () {
    expect(fn() => resolveEndpointAction(PdkEndpoint::CONTEXT_FRONTEND, PdkSharedActions::FETCH_CONTEXT))
        ->toThrow(PdkEndpointException::class, 'Action "fetchContext" does not exist.');
});

it('still resolves fetchContext for the authenticated backend endpoint', function () {
    expect(resolveEndpointAction(PdkEndpoint::CONTEXT_BACKEND, PdkSharedActions::FETCH_CONTEXT))
        ->toBe(FetchContextAction::class);
});

it('still resolves the checkout-context fetcher on the frontend endpoint', function () {
    expect(resolveEndpointAction(PdkEndpoint::CONTEXT_FRONTEND, PdkFrontendActions::FETCH_CHECKOUT_CONTEXT))
        ->toBe(FetchCheckoutContextAction::class);
});

it('still resolves the shared capabilities proxy on the frontend endpoint', function () {
    // The shared fallback is still intended for genuinely public actions such as the capabilities proxy.
    expect(resolveEndpointAction(PdkEndpoint::CONTEXT_FRONTEND, PdkCapabilitiesActions::PROXY_CAPABILITIES))
        ->toBe(CapabilitiesAction::class);
});
