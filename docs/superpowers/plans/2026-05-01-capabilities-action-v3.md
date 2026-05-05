# Capabilities Action — v3 (consolidated with PROXY_CAPABILITIES)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire the JS-PDK admin order-edit form to the shared `PROXY_CAPABILITIES` action (introduced by `feat/add-capabilities-proxy`), so admin-form selection changes drive contextual capability lookups against the live capabilities API. The PHP-side action gets one focused extension — apply the registered-option allowlist server-side — so both admin and frontend callers see the same filtered shape and drift between paths is impossible.

**Architecture:**

- **No new PHP action.** Reuse `CapabilitiesAction` (introduced on `feat/add-capabilities-proxy`, registered as `PdkCapabilitiesActions::PROXY_CAPABILITIES` under `PdkEndpoint::CONTEXT_SHARED`). One narrow change: an **opt-in** body flag (`filterOptions: true`) makes the action apply `Carrier::filterRegisteredOptions` to each capability's `options` before responding — same allowlist `Carrier::attributesToArray` applies to contract-definitions. **Default behavior is unchanged**: without the flag, the action remains a 1:1 passthrough of the SDK response. The admin caller opts in; existing frontend callers (delivery-options widget) continue to receive unfiltered results.
- **No new PHP model, no new PHP Context.** The action returns the SDK's natural `{results: <RefCapabilitiesResponseCapabilityV2[]>}` JSON shape (camelCase via `jsonSerialize`), now with `options` filtered.
- **Optional ergonomics extension:** server-side PDK-camelCase → SDK-snake_case mapping for the most common args (`packageType` → `package_type`, `deliveryType` → `delivery_type`), so JS callers don't have to know SDK conventions. Folded into the same task.
- `DynamicContext` is unchanged — it continues to carry the contract-definition carrier superset for initial page render.
- **JS-PDK admin:** a TanStack query composable fires `BackendEndpoint.ProxyCapabilities` when its selection ref changes (debounced via a watcher). Form helpers (`getCarrier` etc.) prefer the query result when populated, fall back to `DynamicContext.carriers`.
- **No window-focus refetch on the new query.** The selection ref is the only refetch trigger.
- **No form-event invalidation of `DynamicContext`.** Existing call sites that invalidate `[FetchContext, AdminContextKey.Dynamic]` on order-form events get audited and removed.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models. JS-PDK monorepo: TypeScript, Vue 3, TanStack Query, Vitest.

**Jira:** [INT-1505](https://myparcelnl.atlassian.net/browse/INT-1505) (sub-task of [INT-930](https://myparcelnl.atlassian.net/browse/INT-930))

**Branch (PHP PDK):** `feat/INT-1505-admin-capabilities-context` (existing — currently 1 commit ahead of `feat/INT-1501-capabilities-order-calculators`: the `Carrier::filterRegisteredOptions` extract)
**Branch (JS-PDK):** `feat/INT-1505-admin-capabilities-context` (new, off `capabilities`)

---

## Hard dependency: `feat/add-capabilities-proxy` must merge first

This plan assumes `feat/add-capabilities-proxy` (the branch introducing `PROXY_CAPABILITIES`) has been **rebased onto `v4-capabilities` and merged**. That branch contributes:

- `src/Api/PdkCapabilitiesActions.php` with `PROXY_CAPABILITIES = 'proxyCapabilities'`
- `src/App/Action/Capabilities/CapabilitiesAction.php` (the proxy action: SDK passthrough with CORS handling)
- `src/App/Request/Capabilities/CapabilitiesEndpointRequest.php`
- Wiring in `config/actions.php` under `PdkEndpoint::CONTEXT_SHARED`

Until that merges into `v4-capabilities`, this plan is blocked at Task A1. Don't start Phase A until the proxy branch is in.

JS-PDK side: `feat/add-proxy-capabilities-endpoint` (already on `origin`) declares `FrontendEndpoint.ProxyCapabilities = 'proxyCapabilities'`. Phase B introduces the `BackendEndpoint.ProxyCapabilities` counterpart so admin code can reference the same action through admin-typed channels.

---

## Background — the journey here

Earlier iterations on this branch tried to bake the contextual data into `DynamicContext`:

- **v1**: stored projection state on the `Carrier` model. Rejected for polluting the model + alternate serialization paths.
- **v2**: filtered `DynamicContext.carriers` and overrode serialization. The `'carriers' => CarrierCollection::class` cast forced re-serialization through `Carrier::attributesToArray` (contract-definition shape). Working around it required an alternate serialization path on `DynamicContext`, which the user rejected.
- **earlier v3 draft**: introduced a fresh `OrderCapabilitiesContext` PDK model + a new `FetchCapabilitiesAction`. The user pointed out the model wraps an SDK response 1:1 (no PDK compatibility layer needed) and the new action duplicates `feat/add-capabilities-proxy`. Both observations led to this revision.

What survives:

- `Carrier::filterRegisteredOptions` (commit `55ad4313`) — the public static that holds the option allowlist. Now applied inside the existing proxy action.

---

## Project conventions (read first — not optional)

This plan assumes you have no memory from prior sessions.

### Working environment

- **Platform:** macOS host, Linux container via Docker.
- **Repos:**
  - PHP PDK: `/Users/freek.vanrijt/projects/pdk`.
  - JS-PDK: `/Users/freek.vanrijt/projects/js-pdk` (monorepo; Yarn + Nx).
- **PHP tests (Pest v1, no `describe()` / `arch()` / `covers()`):**
  - Full: `docker compose run --rm php composer test:unit`
  - Filter: `docker compose run --rm php composer test:unit -- --filter="test name"`
- **Multi-PHP:** `PHP_VERSION=X.Y docker compose run --rm php composer update --no-interaction --no-progress && PHP_VERSION=X.Y docker compose run --rm php composer test:unit`. Always `composer update` first when switching versions.
- **PHPStan:** `docker compose run --rm php composer analyse`. No new errors in modified files.
- **JS-PDK:** from `~/projects/js-pdk`: `yarn test`, `yarn lint`, `yarn typecheck`, `yarn build`.

### Code style (PHP)

- No sentinel values. Nullable types + explicit null handling.
- No algorithm jargon in comments.
- No unused foreach values: `foreach (array_keys($arr) as $key)`, not `foreach ($arr as $key => $_)`.
- Comments explain intent, not mechanics.
- No hardcoded carrier names.
- SDK PHPStan-ignore patterns where needed: `// @phpstan-ignore-line SDK declares non-nullable but runtime may be null`.

### Code style (TypeScript)

- Match Prettier + ESLint. Prefer `type` over `interface`. No `as any`.
- No hardcoded carrier names.

### Commit conventions

- Conventional commits: `feat(scope)!:`, `fix(scope):`, `chore:`, `test:`, `docs:`.
- Tests + PHPStan before committing.

---

## Behavioral test matrix

### Existing behavior (must not regress)

| #   | Scenario                                                                                                | Expected                                                                  |
| --- | ------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------- |
| 1   | Carrier settings page                                                                                   | Unchanged — DynamicContext only; new query never fires                    |
| 2   | Order edit modal, `isRequired` option                                                                   | Field disabled + auto-checked (`OrderDataContextCapabilitiesTest` passes) |
| 3   | Order edit modal, `isSelectedByDefault`                                                                 | Field pre-checked when no other setting                                   |
| 4   | Order edit modal, carrier setting disables option                                                       | Field disabled                                                            |
| 5   | V1 shipment / return / fulfilment                                                                       | Unaffected                                                                |
| 6   | Form helpers (`getCarrier`, `getPackageTypes`, `hasShipmentOption`, `getInsuranceOptions`)              | Same shape they read today                                                |
| 7   | Existing frontend caller of `PROXY_CAPABILITIES` (delivery-options widget) without `filterOptions` flag | Unchanged — receives the SDK's unfiltered response (default passthrough)  |

### New behavior

| #   | Scenario                                                                   | Expected                                                                                                                                                                  |
| --- | -------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 8   | Order page mounts                                                          | JS fires `ProxyCapabilities` POST with body `{cc, weight, carrier?, packageType?, deliveryType?, options[]?, filterOptions: true}` (admin opts in to allowlist filtering) |
| 9   | Action returns                                                             | TanStack cache populated with `{results: CarrierModel[]}` (one entry per matching carrier)                                                                                |
| 10  | Form helper `getCarrier(form)` after action returns                        | Reads from the new query's data; falls back to `DynamicContext.carriers` when query has no data                                                                           |
| 11  | Merchant changes selected carrier / packageType / deliveryType / options   | Watcher debounces 300ms → selection ref changes → query refetches → cache updates → helpers re-evaluate                                                                   |
| 12  | Order has empty `shippingAddress.cc`                                       | Query is disabled (`enabled: false`); helpers fall back to DynamicContext                                                                                                 |
| 13  | `results[i].options[X].requires` / `excludes`                              | Present when API returns them (SDK serializes natively)                                                                                                                   |
| 14  | `results[i].options.insurance.insuredAmount`                               | `min/max/default` present when API returns insurance amounts                                                                                                              |
| 15  | Action's `results[i].options` when `filterOptions: true` body flag is set  | Only contains keys backed by a registered `OrderOptionDefinitionInterface`                                                                                                |
| 15a | Action's `results[i].options` when `filterOptions` flag is absent or false | Returns the SDK's full unfiltered options (default passthrough)                                                                                                           |
| 16  | Settings page                                                              | Query never fires; only DynamicContext present                                                                                                                            |
| 17  | Order edit view: form-state changes (carrier / packageType / options)      | Dynamic context is NOT refetched (form-event invalidations of Dynamic removed in B6)                                                                                      |
| 18  | Window focus on any view                                                   | New query does NOT refetch on focus (`refetchOnWindowFocus: false`)                                                                                                       |

---

## File structure

### PHP PDK (modified)

| File                                                            | Action                                                                                                                                               |
| --------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `src/App/Action/Capabilities/CapabilitiesAction.php`            | Modify — apply `Carrier::filterRegisteredOptions` per capability; optional PDK-camelCase → SDK-snake_case mapping for `packageType` / `deliveryType` |
| `tests/Unit/App/Action/Capabilities/CapabilitiesActionTest.php` | Extend (or create) — assert allowlist filter applied; assert PDK names accepted                                                                      |

That's it on the PHP side. **No new model, no new context, no new action class.**

### JS-PDK (created / modified)

| File                                                                                      | Action                                                                                                                       |
| ----------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| `libs/common/src/data/endpoints.ts`                                                       | Add `BackendEndpoint.ProxyCapabilities = 'proxyCapabilities'` (counterpart to existing `FrontendEndpoint.ProxyCapabilities`) |
| `apps/admin/src/types/sdk.types.ts`                                                       | Add admin `ProxyCapabilitiesDefinition` to discriminated union                                                               |
| `apps/admin/src/types/actions/endpoints.types.ts` + `parameters.types.ts`                 | Wire the endpoint into admin action types                                                                                    |
| `apps/admin/src/data/AdminContextKey.ts` (or wherever the enum lives)                     | Add `AdminContextKey.Capabilities` (cache-routing key only — no PHP coupling)                                                |
| `apps/admin/src/actions/composables/queries/account/useProxyCapabilitiesQuery.ts`         | Create — TanStack query composable                                                                                           |
| `apps/admin/src/forms/shipmentOptions/useCapabilitiesWatcher.ts`                          | Create — debounced watcher merging order ref + form ref into the selection ref                                               |
| `apps/admin/src/forms/shipmentOptions/createShipmentOptionsForm.ts`                       | Wire watcher + query into the form lifecycle                                                                                 |
| `apps/admin/src/forms/helpers/getCarrier.ts`                                              | Prefer the new query's data when populated; fall back to DynamicContext                                                      |
| Existing form-event subscribers that invalidate `[FetchContext, AdminContextKey.Dynamic]` | Audit and remove the form-driven invalidations                                                                               |

---

## Phase A — PHP

### Task A1: Add opt-in allowlist filter to `CapabilitiesAction`

**Files:**

- Modify: `src/App/Action/Capabilities/CapabilitiesAction.php`
- Test: `tests/Unit/App/Action/Capabilities/CapabilitiesActionTest.php` (extend or create — depends on what `feat/add-capabilities-proxy` already includes)

**Responsibility:**

1. **Opt-in option allowlist (default off).** Read a `filterOptions` boolean from the request body (NOT from the args forwarded to the SDK). When `true`, apply `Carrier::filterRegisteredOptions` to each capability's `options` before encoding the response. When absent or false, the action remains a 1:1 SDK passthrough — preserves existing frontend caller behavior.
2. **Optional ergonomics: PDK-camelCase → SDK-snake_case mapping** for `packageType` / `deliveryType`. Always-on (additive — existing SDK-style callers send `package_type` directly and pass through unchanged).
3. The `filterOptions` key MUST be stripped from the payload before forwarding to `CapabilitiesService::getCapabilities()` so the SDK doesn't reject the unknown arg.

The proxy action currently does (per inspection of `feat/add-capabilities-proxy`):

```php
public function handle(Request $request): Response
{
    // CORS handling …
    $payload  = $this->getRequestData($request);
    $results  = $this->capabilitiesService->getCapabilities($payload);
    $response = $this->createSymfonyResponse($results);
    // …
}

private function createSymfonyResponse(array $results): Response
{
    return new Response(
        json_encode(['results' => $results]),
        Response::HTTP_OK,
        ['Content-Type' => 'application/json']
    );
}
```

The filter step happens between `getCapabilities` (returning `RefCapabilitiesResponseCapabilityV2[]`) and `createSymfonyResponse` (encoding to JSON). Walk each capability's options, filter, and put the filtered array back.

- [ ] **Step 1: Verify the proxy branch is merged into `v4-capabilities`**

```bash
cd ~/projects/pdk
git fetch origin v4-capabilities
git log --oneline origin/v4-capabilities | grep -i "capabilities-proxy\|PROXY_CAPABILITIES" | head -3
```

If empty, **stop**. The proxy branch hasn't merged yet — this plan is blocked.

Verify the action class exists on `v4-capabilities`:

```bash
git show origin/v4-capabilities:src/App/Action/Capabilities/CapabilitiesAction.php | head -20
```

- [ ] **Step 2: Rebase `feat/INT-1505-admin-capabilities-context` onto post-proxy `v4-capabilities`**

Already on the branch and ahead of `feat/INT-1501`. The proxy work has now landed on `v4-capabilities`, so `feat/INT-1501` (the parent of this branch) needs to rebase first, then this branch follows. If the stack ordering changes once the proxy lands, follow the existing rebase pattern in the repo's session memory (`project_int930_revert_files.md`, etc.).

```bash
git checkout feat/INT-1501-capabilities-order-calculators
git pull --rebase origin v4-capabilities
git push --force-with-lease

git checkout feat/INT-1505-admin-capabilities-context
git rebase feat/INT-1501-capabilities-order-calculators
git push --force-with-lease
```

If conflicts surface, resolve and continue the rebase — the only commits on this branch are the v3 plan doc + `Carrier::filterRegisteredOptions` extract.

- [ ] **Step 3: Read the current `CapabilitiesAction` body**

```bash
cat src/App/Action/Capabilities/CapabilitiesAction.php
```

Note exactly where the `getCapabilities` result is captured before being passed to `createSymfonyResponse` — that's the seam where the filter goes.

- [ ] **Step 4: Write a failing test**

Either extend the existing `CapabilitiesActionTest` (if `feat/add-capabilities-proxy` shipped one) or create one. Use Pest v1 conventions, `usesShared(new UsesMockPdkInstance(), new UsesAccountMock())`, and rely on `MockCarrierCapabilitiesRepository`'s permissive default (no `MockSdkApiHandler::enqueue` needed when `UsesSdkApiMock` isn't active).

```php
<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('passes options through unchanged by default (no filterOptions flag)', function () {
    factory(Carrier::class)->withAllCapabilities()->store();

    $body = json_encode(['cc' => 'NL']);

    $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

    $response = (Pdk::get(CapabilitiesAction::class))->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($payload['results'])->toBeArray()->and($payload['results'])->not->toBeEmpty();

    // Without the opt-in flag, the action is a 1:1 SDK passthrough — option keys are NOT filtered.
    // The mock returns a permissive set; assert at least one capability has options that include
    // any unregistered keys the SDK would have emitted (here we just assert the array is non-empty
    // and not pre-filtered).
    foreach ($payload['results'] as $capability) {
        expect($capability)->toHaveKey('options');
    }
});

it('filters options to registered keys when filterOptions=true is in the body', function () {
    factory(Carrier::class)->withAllCapabilities()->store();

    $body = json_encode([
        'cc'            => 'NL',
        'filterOptions' => true,
    ]);

    $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

    $response = (Pdk::get(CapabilitiesAction::class))->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($payload['results'])->toBeArray()->and($payload['results'])->not->toBeEmpty();

    foreach ($payload['results'] as $capability) {
        foreach (array_keys($capability['options'] ?? []) as $optionKey) {
            // Every emitted option key passes the same allowlist Carrier::attributesToArray applies.
            expect(Carrier::filterRegisteredOptions([$optionKey => []]))->toHaveKey($optionKey);
        }
    }
});

it('accepts PDK-camelCase packageType and forwards SDK-snake_case to the API', function () {
    factory(Carrier::class)->withAllCapabilities()->store();

    $body = json_encode([
        'cc'          => 'NL',
        'packageType' => 'PACKAGE',
    ]);

    $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

    $response = (Pdk::get(CapabilitiesAction::class))->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($payload['results'])->toBeArray()->and($payload['results'])->not->toBeEmpty();
});

it('does not forward filterOptions to the SDK args (control flag must be stripped before calling getCapabilities)', function () {
    factory(Carrier::class)->withAllCapabilities()->store();

    $body = json_encode([
        'cc'            => 'NL',
        'filterOptions' => true,
    ]);

    $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

    // If filterOptions leaks into the SDK args, the SDK call will throw or fail. Successful response
    // (HTTP 200, results array present) implicitly asserts the strip happened.
    $response = (Pdk::get(CapabilitiesAction::class))->handle($request);

    expect($response->getStatusCode())->toBe(200);
});
```

- [ ] **Step 5: Run — expect failure**

```bash
docker compose run --rm php composer test:unit -- --filter="CapabilitiesActionTest"
```

- [ ] **Step 6: Implement the opt-in filter + name mapping**

Modify `src/App/Action/Capabilities/CapabilitiesAction.php`:

1. Imports:

```php
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
```

2. **Extract `filterOptions` from the payload + map PDK names to SDK names BEFORE forwarding.** The `filterOptions` flag is a control parameter, not an SDK arg — it must be stripped so the SDK doesn't receive an unknown key.

```php
/**
 * Pull the action's control parameter ($filterOptions) out of the payload and translate any
 * PDK-camelCase argument names to SDK-snake_case so callers don't have to know SDK conventions.
 *
 * @param  array $payload
 *
 * @return array{0: array, 1: bool} [SDK args (control flag stripped), shouldFilterOptions]
 */
private function prepareSdkArgs(array $payload): array
{
    $shouldFilterOptions = ! empty($payload['filterOptions']);
    unset($payload['filterOptions']);

    if (isset($payload['packageType'])) {
        $payload['package_type'] = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$payload['packageType']]
            ?? $payload['packageType'];
        unset($payload['packageType']);
    }

    if (isset($payload['deliveryType'])) {
        $payload['delivery_type'] = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$payload['deliveryType']]
            ?? $payload['deliveryType'];
        unset($payload['deliveryType']);
    }

    return [$payload, $shouldFilterOptions];
}
```

3. Update `handle` so `prepareSdkArgs` runs before the API call. Pass the `shouldFilterOptions` flag through to the response builder:

```php
public function handle(Request $request): Response
{
    $corsHandler = Pdk::get(CorsHandler::class);

    if ($request->isMethod('OPTIONS')) {
        return $corsHandler->handlePreflightRequest($request) ?? new Response();
    }

    $rawPayload = $this->getRequestData($request);
    [$sdkArgs, $shouldFilterOptions] = $this->prepareSdkArgs($rawPayload);

    try {
        $results  = $this->capabilitiesService->getCapabilities($sdkArgs);
        $response = $this->createSymfonyResponse($results, $shouldFilterOptions);
    } catch (Throwable $e) {
        $response = $this->createErrorResponse($e);
    }

    return $corsHandler->addCorsHeaders($request, $response);
}
```

4. Apply the allowlist filter only when the flag is set:

```php
private function createSymfonyResponse(array $results, bool $filterOptions = false): Response
{
    $body = json_decode(json_encode(['results' => $results]), true);

    if ($filterOptions && isset($body['results']) && is_array($body['results'])) {
        foreach ($body['results'] as &$capability) {
            if (isset($capability['options']) && is_array($capability['options'])) {
                $capability['options'] = Carrier::filterRegisteredOptions($capability['options']);
            }
        }
        unset($capability);
    }

    return new Response(
        json_encode($body),
        Response::HTTP_OK,
        ['Content-Type' => 'application/json']
    );
}
```

> The default value `false` on `createSymfonyResponse` keeps existing callers (and any tests on `feat/add-capabilities-proxy`) working without modification — they pass nothing for the second arg, get the unfiltered passthrough.

- [ ] **Step 7: Run + PHPStan**

```bash
docker compose run --rm php composer test:unit -- --filter="CapabilitiesActionTest"
docker compose run --rm php vendor/bin/phpstan analyse src/App/Action/Capabilities/CapabilitiesAction.php --no-progress
```

Both pass. PHPStan no new errors.

- [ ] **Step 8: Run full suite to catch regressions**

```bash
docker compose run --rm php composer test:unit 2>&1 | grep -E "Tests:" | tail -1
```

0 failures.

- [ ] **Step 9: Commit**

```bash
git add src/App/Action/Capabilities/CapabilitiesAction.php tests/Unit/App/Action/Capabilities/CapabilitiesActionTest.php
git commit -m "$(cat <<'EOF'
feat(capabilities): apply registered-option allowlist + accept PDK-camelCase args

CapabilitiesAction now applies Carrier::filterRegisteredOptions to each
capability's options before encoding the response — same allowlist that
Carrier::attributesToArray applies to contract-definitions, so JS form
helpers see consistent option keys regardless of which path produced
the data.

Also accepts packageType / deliveryType (PDK camelCase) alongside the
SDK snake_case names, mapping at the action boundary so admin and
frontend callers don't have to know SDK conventions.

Refs: INT-1505
EOF
)"
```

---

### Task A2: Multi-PHP verification + force-push

- [ ] **Step 1: PHP 7.4 / 8.0 / 8.4** — for each:

```bash
PHP_VERSION=X.Y docker compose run --rm php composer update --no-interaction --no-progress
PHP_VERSION=X.Y docker compose run --rm php composer test:unit
```

All pass. No commit.

- [ ] **Step 2: Restore PHP 7.4 default**

```bash
PHP_VERSION=7.4 docker compose run --rm php composer update --no-interaction --no-progress
```

- [ ] **Step 3: Force-push**

```bash
git push --force-with-lease origin feat/INT-1505-admin-capabilities-context
```

- [ ] **Step 4: Verify PR #453 mergeable**

```bash
gh pr view 453 --json mergeable,state
```

Expected: MERGEABLE.

---

## Phase B — JS-PDK

**Branch:** off `capabilities`.

```bash
cd ~/projects/js-pdk
git fetch origin
git checkout -b feat/INT-1505-admin-capabilities-context origin/capabilities
```

> If `feat/add-proxy-capabilities-endpoint` (the JS-PDK side of the proxy work) merges into `capabilities` first, branch off the merge commit. If the proxy branch is still separate, this plan adds the admin-side entry independently — `BackendEndpoint.ProxyCapabilities` parallels the existing `FrontendEndpoint.ProxyCapabilities` and points at the same backend route.

### Task B1: Add `BackendEndpoint.ProxyCapabilities` + types

**Files:**

- Modify: `libs/common/src/data/endpoints.ts`
- Modify: `apps/admin/src/types/sdk.types.ts`
- Modify: `apps/admin/src/types/actions/endpoints.types.ts` + `parameters.types.ts`

The TanStack cache key for the new query reuses `BackendEndpoint.ProxyCapabilities` directly (existing pattern in `useFetchWebhooksQuery`). **Do not** add a `Capabilities` entry to `AdminContextKey` — that enum is reserved for real PHP-rendered contexts; mixing in JS-only cache keys misleads implementers into looking for a server-side context that doesn't exist.

- [ ] **Step 1: Backend endpoint constant**

In `libs/common/src/data/endpoints.ts`, add to the `BackendEndpoint` enum:

```ts
ProxyCapabilities = 'proxyCapabilities',
```

> The string value matches `PdkCapabilitiesActions::PROXY_CAPABILITIES = 'proxyCapabilities'`. The PHP side registers under `PdkEndpoint::CONTEXT_SHARED`, so admin (backend) and frontend callers both resolve to the same action; only the JS-side enum constant differs by surface.

- [ ] **Step 2: SDK definition for the new admin endpoint**

In `apps/admin/src/types/sdk.types.ts`:

```ts
import { type CarrierModel } from '@myparcel-dev/pdk-common';

interface ProxyCapabilitiesDefinition extends PdkEndpointDefinition {
  name: BackendEndpoint.ProxyCapabilities;
  parameters: Record<string, never>;
  body: {
    cc: string;
    weight?: number;
    carrier?: string;
    packageType?: string;
    deliveryType?: string;
    options?: string[];
    /**
     * Opt-in: when true, the action applies the registered-option allowlist
     * (`Carrier::filterRegisteredOptions`) to each capability's options. Default behavior
     * (flag absent or false) is unfiltered SDK passthrough.
     */
    filterOptions?: boolean;
  };
  response: { results: CarrierModel[] };
  formattedResponse: CarrierModel[];
}
```

Add `ProxyCapabilitiesDefinition` to the discriminated-union of admin endpoint definitions in the same file.

- [ ] **Step 3: Wire endpoints + parameters**

In `apps/admin/src/types/actions/endpoints.types.ts`, add `BackendEndpoint.ProxyCapabilities` to the admin endpoint union. In `parameters.types.ts`, map it to the body type.

- [ ] **Step 4: typecheck + commit**

```bash
yarn nx typecheck admin
git add libs/common/src/data/endpoints.ts apps/admin/src/types/
git commit -m "feat(admin): add BackendEndpoint.ProxyCapabilities and admin SDK definition"
```

---

### Task B2: `useProxyCapabilitiesQuery`

**Files:**

- Create: `apps/admin/src/actions/composables/queries/account/useProxyCapabilitiesQuery.ts`
- Create: `apps/admin/src/actions/composables/queries/account/useProxyCapabilitiesQuery.test.ts`

**Responsibility:** TanStack Query composable. Takes a Vue ref of the current selection (`{cc, weight, carrier?, packageType?, deliveryType?, options[]?}`); fires the backend action when `cc` is present; cache-keyed by selection so changes invalidate naturally; returns the `CarrierModel[]` array (from `response.results`).

**Refetch semantics — important.** The selection ref is the ONLY refetch trigger. No window-focus, no on-mount, no on-reconnect:

```ts
{
  refetchOnWindowFocus: false,
  refetchOnMount: false,
  refetchOnReconnect: false,
}
```

- [ ] **Step 1: Failing test**

```ts
import { describe, it, expect, vi } from 'vitest';
import { ref } from 'vue';
import { useProxyCapabilitiesQuery } from './useProxyCapabilitiesQuery';

const proxyCapabilities = vi.fn(async () => ({ results: [] }));

vi.mock('../../../../sdk', () => ({
  usePdkAdminApi: () => ({ proxyCapabilities }),
}));

describe('useProxyCapabilitiesQuery', () => {
  it('does not fire when cc is missing', () => {
    const selection = ref<{ cc?: string }>({});

    useProxyCapabilitiesQuery(selection);

    expect(proxyCapabilities).not.toHaveBeenCalled();
  });

  it('fires with the current selection in the body when cc is present', async () => {
    const selection = ref({ cc: 'NL', weight: 2000, carrier: 'POSTNL' });

    useProxyCapabilitiesQuery(selection);

    await Promise.resolve();

    expect(proxyCapabilities).toHaveBeenCalled();
    const callArg = (proxyCapabilities as any).mock.calls[0]?.[0];
    expect(callArg).toBeTruthy();
    // Adapt assertion to wrapper's actual call signature (e.g. `body` parameter shape).
  });
});
```

- [ ] **Step 2: Run — expect failure**

```bash
yarn nx test admin -- --run useProxyCapabilitiesQuery
```

- [ ] **Step 3: Implement**

```ts
import { useQuery } from '@tanstack/vue-query';
import { AdminContextKey, BackendEndpoint, type CarrierModel } from '@myparcel-dev/pdk-common';
import { computed, type Ref } from 'vue';
import { usePdkAdminApi } from '../../../../sdk';

export type CapabilitiesSelection = {
  cc?: string;
  weight?: number;
  carrier?: string;
  packageType?: string;
  deliveryType?: string;
  options?: string[];
};

export const useProxyCapabilitiesQuery = (selection: Ref<CapabilitiesSelection>) => {
  const enabled = computed(() => Boolean(selection.value.cc));
  const queryKey = computed(() => [BackendEndpoint.ProxyCapabilities, AdminContextKey.Capabilities, selection.value]);

  return useQuery(
    queryKey,
    async (): Promise<CarrierModel[]> => {
      const pdk = usePdkAdminApi();
      const response = await pdk.proxyCapabilities({
        // @ts-expect-error custom endpoints are not typed correctly across all helpers
        body: {
          ...selection.value,
          // Opt in to server-side option allowlist filtering. The proxy action's default behavior
          // is unfiltered passthrough (preserves the delivery-options widget caller); admin needs
          // the same allowlist Carrier::attributesToArray applies, so the JS sees a stable
          // option set across both data sources.
          filterOptions: true,
        },
      });
      return response.results ?? [];
    },
    {
      enabled,
      refetchOnWindowFocus: false,
      refetchOnMount: false,
      refetchOnReconnect: false,
    },
  );
};
```

- [ ] **Step 4: Run + lint + commit**

```bash
yarn nx test admin -- --run useProxyCapabilitiesQuery
yarn nx lint admin
git add apps/admin/src/actions/composables/queries/account/useProxyCapabilitiesQuery.*
git commit -m "feat(admin): add useProxyCapabilitiesQuery composable"
```

---

### Task B3: `useCapabilitiesWatcher` (debounced)

**Files:**

- Create: `apps/admin/src/forms/shipmentOptions/useCapabilitiesWatcher.ts`
- Create: `apps/admin/src/forms/shipmentOptions/useCapabilitiesWatcher.test.ts`

**Responsibility:** Given an order ref (`{cc, weight}`) and a form ref (`{carrier, packageType, deliveryType, options[]}`), produce a debounced (300ms) ref of the merged selection. Collapses rapid form-state changes into one query refetch.

- [ ] **Step 1: Failing test**

```ts
import { describe, it, expect, vi } from 'vitest';
import { ref, nextTick } from 'vue';
import { useCapabilitiesWatcher } from './useCapabilitiesWatcher';

describe('useCapabilitiesWatcher', () => {
  it('collapses rapid form-state changes into one selection update after debounce', async () => {
    vi.useFakeTimers();

    const orderRef = ref({ cc: 'NL', weight: 2000 });
    const formRef = ref({ carrier: 'POSTNL', packageType: 'PACKAGE' });

    const selectionRef = useCapabilitiesWatcher(orderRef, formRef);

    expect(selectionRef.value).toMatchObject({ cc: 'NL', weight: 2000, carrier: 'POSTNL', packageType: 'PACKAGE' });

    formRef.value = { carrier: 'DHL_FOR_YOU', packageType: 'PACKAGE' };
    await nextTick();
    vi.advanceTimersByTime(150);

    formRef.value = { carrier: 'DHL_FOR_YOU', packageType: 'MAILBOX' };
    await nextTick();
    vi.advanceTimersByTime(300);

    expect(selectionRef.value).toMatchObject({ carrier: 'DHL_FOR_YOU', packageType: 'MAILBOX' });

    vi.useRealTimers();
  });
});
```

- [ ] **Step 2: Run — expect failure**

```bash
yarn nx test admin -- --run useCapabilitiesWatcher
```

- [ ] **Step 3: Implement**

```ts
import { ref, watch, type Ref } from 'vue';
import { type CapabilitiesSelection } from '../../actions/composables/queries/account/useProxyCapabilitiesQuery';

const DEBOUNCE_MS = 300;

export type OrderInput = { cc: string; weight?: number };
export type FormInput = { carrier?: string; packageType?: string; deliveryType?: string; options?: string[] };

export const useCapabilitiesWatcher = (
  orderRef: Ref<OrderInput>,
  formRef: Ref<FormInput>,
): Ref<CapabilitiesSelection> => {
  const selection = ref<CapabilitiesSelection>({ ...orderRef.value, ...formRef.value });

  let timer: ReturnType<typeof setTimeout> | null = null;

  watch(
    [orderRef, formRef],
    () => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => {
        selection.value = { ...orderRef.value, ...formRef.value };
        timer = null;
      }, DEBOUNCE_MS);
    },
    { deep: true },
  );

  return selection;
};
```

- [ ] **Step 4: Run + commit**

```bash
yarn nx test admin -- --run useCapabilitiesWatcher
git add apps/admin/src/forms/shipmentOptions/useCapabilitiesWatcher.*
git commit -m "feat(admin): add debounced capabilities watcher"
```

---

### Task B4: `getCarrier` prefers the new query's data

**Files:**

- Modify: `apps/admin/src/forms/helpers/getCarrier.ts`

**Responsibility:** When the new query has data (length > 0), find the chosen carrier in that list. Otherwise fall back to `dynamicContext.carriers`.

- [ ] **Step 1: Inspect the existing read mechanism**

```bash
cd ~/projects/js-pdk
grep -rln "useContext(AdminContextKey" apps/admin/src/composables apps/admin/src/forms 2>&1 | head -5
```

- [ ] **Step 2: Modify `getCarrier.ts`**

```ts
import { type FormInstance } from '@myparcel-dev/vue-form-builder';
import { AdminContextKey, type CarrierModel } from '@myparcel-dev/pdk-common';
import { FIELD_CARRIER } from '../shipmentOptions';
import { useContext } from '../../composables';

export const getCarrier = (form: FormInstance): CarrierModel | undefined => {
  const capabilities = useContext(AdminContextKey.Capabilities);
  const dynamicContext = useContext(AdminContextKey.Dynamic);
  const chosenCarrier = form.getValue(FIELD_CARRIER);

  const fromCapabilities = (capabilities as CarrierModel[] | undefined)?.find?.(
    (carrier) => carrier.carrier === chosenCarrier,
  );
  if (fromCapabilities) {
    return fromCapabilities;
  }

  return dynamicContext.carriers.find((carrier) => carrier.carrier === chosenCarrier);
};
```

> The shape returned by `useContext(AdminContextKey.Capabilities)` depends on what we wire in B5. Treat as `CarrierModel[]`.

- [ ] **Step 3: Run + commit**

```bash
yarn nx test admin -- --run getCarrier
git add apps/admin/src/forms/helpers/getCarrier.ts
git commit -m "feat(admin): getCarrier prefers ProxyCapabilities query data when populated"
```

---

### Task B5: Wire watcher + query into the shipment-options form

**Files:**

- Modify: `apps/admin/src/forms/shipmentOptions/createShipmentOptionsForm.ts`

**Responsibility:**

1. Build an `orderRef` from the order's `shippingAddress.cc` + cart weight.
2. Build a `formRef` from the form's carrier / packageType / deliveryType / options field values.
3. Call `useCapabilitiesWatcher(orderRef, formRef)` → `selectionRef`.
4. Call `useProxyCapabilitiesQuery(selectionRef)`.
5. The query writes its result into the cache keyed by `[BackendEndpoint.ProxyCapabilities, AdminContextKey.Capabilities, ...]`. The `getCarrier` helper (B4) reads from the same key.

Inspect how `useFetchContextQuery` writes its result into the store (likely via `setQueryData` keyed by `[BackendEndpoint.FetchContext, AdminContextKey.Dynamic]`). Mirror the wiring for `[BackendEndpoint.ProxyCapabilities, AdminContextKey.Capabilities]`.

- [ ] **Step 1: Add imports**

```ts
import { useProxyCapabilitiesQuery } from '../../actions/composables/queries/account/useProxyCapabilitiesQuery';
import { useCapabilitiesWatcher } from './useCapabilitiesWatcher';
```

- [ ] **Step 2: Add wiring inside `createShipmentOptionsForm`**

```ts
const orderRef = computed(() => ({
  cc: order?.shippingAddress?.cc ?? '',
  weight: order?.physicalProperties?.totalWeight,
}));

const formRef = computed(() => ({
  carrier: form.getValue(FIELD_CARRIER),
  packageType: form.getValue(FIELD_PACKAGE_TYPE),
  deliveryType: form.getValue(FIELD_DELIVERY_TYPE),
  options: allOptionKeys.filter((key) => Boolean(form.getValue(`${FIELD_SHIPMENT_OPTIONS_PREFIX}.${key}`))),
}));

const selectionRef = useCapabilitiesWatcher(orderRef, formRef);
useProxyCapabilitiesQuery(selectionRef);
```

> Verify the actual symbols in scope (`form`, `order`, `allOptionKeys`, `FIELD_*`) and adjust accordingly.

- [ ] **Step 3: typecheck + lint + tests**

```bash
yarn nx typecheck admin
yarn nx lint admin
yarn nx test admin
```

- [ ] **Step 4: Commit**

```bash
git add apps/admin/src/forms/shipmentOptions/createShipmentOptionsForm.ts
git commit -m "feat(admin): wire ProxyCapabilities query into shipment options form"
```

---

### Task B6: Audit Dynamic invalidations on order-form events

**Files:**

- Modify: any form-event subscriber that invalidates `[BackendEndpoint.FetchContext, AdminContextKey.Dynamic]` on order-edit form changes

**Responsibility:** Some order-form events currently fire `invalidateQueries` against the Dynamic query, causing wasted refetches. Dynamic carries contract-definition data — it doesn't depend on form state. Audit and remove the form-event-driven invalidations.

**Out of scope:** window-focus refetch behavior. The new query never refetches on focus (set explicitly in B2). Existing `useFetchContextQuery` window-focus on settings views is unchanged.

- [ ] **Step 1: Find all invalidations targeting Dynamic**

```bash
cd ~/projects/js-pdk
grep -rn "invalidateQueries.*FetchContext\|invalidateQueries.*AdminContextKey\.Dynamic\|setQueryData.*FetchContext.*Dynamic" apps/admin/src 2>&1
```

- [ ] **Step 2: Classify each call site**

- **Remove** if triggered by a form event.
- **Keep** if triggered by a non-form event that legitimately refreshes contract-definition data.
- **Retarget to ProxyCapabilities** only if the intent was contextual data refresh (rarely needed — B2's selection-ref reactivity handles this).

- [ ] **Step 3: Apply + commit**

```bash
yarn nx test admin
git add <each modified file>
git commit -m "$(cat <<'EOF'
chore(admin): stop wasted Dynamic refetches on order-form events

Audited invalidateQueries call sites targeting
[BackendEndpoint.FetchContext, AdminContextKey.Dynamic]. Removed those
fired by order-form events — Dynamic carries contract-definition data
that doesn't depend on form state. Contextual carrier data lives on
the new ProxyCapabilities query (Tasks B1–B5) and refreshes only when
its selection ref changes (no window-focus, no on-mount).

Refs: INT-1505
EOF
)"
```

---

### Task B7: Build + manual smoke + open PR

- [ ] **Step 1: Full check**

```bash
yarn typecheck && yarn lint && yarn test && yarn build
```

- [ ] **Step 2: Manual verification in WC or PrestaShop**

Verify in admin order edit:

1. **Page mount** — `data-pdk-context.dynamic.carriers[*]` is the contract-definition superset. Network: POST to `proxyCapabilities` fires shortly after mount with `{cc, weight, ...}`.
2. **Cache populated** — TanStack cache contains a `CarrierModel[]` keyed by `[BackendEndpoint.ProxyCapabilities, AdminContextKey.Capabilities, ...]`.
3. **`getCarrier(form)` reads new data** — option metadata reflects contextual response.
4. **Switch carrier** — debounce ~300ms → query refetches → helpers re-evaluate.
5. **Empty cc** — query disabled; helpers fall back to dynamic context.
6. **Settings page** — query never fires.
7. **Window focus** — capabilities query does NOT refetch.
8. **Order-form events** — Dynamic does NOT refetch.

- [ ] **Step 3: Push + open JS-PDK PR**

```bash
git push -u origin feat/INT-1505-admin-capabilities-context
```

Title: `feat(admin): contextual capabilities for admin order-edit [INT-1505]`

Body:

```markdown
## Summary

- Adds `BackendEndpoint.ProxyCapabilities` (counterpart to existing `FrontendEndpoint.ProxyCapabilities`) and `AdminContextKey.Capabilities` cache key.
- New `useProxyCapabilitiesQuery` + `useCapabilitiesWatcher` fire the existing `PROXY_CAPABILITIES` action when the order's selection changes (debounced 300ms).
- `getCarrier` form helper prefers the new query's data when populated; falls back to `DynamicContext.carriers`.
- Audits and removes form-event invalidations of Dynamic — Dynamic carries contract-definition data unaffected by form state.
- No window-focus refetch on the new query.

Pairs with PDK PR #453.

## Test plan

- [ ] `yarn typecheck`, `yarn lint`, `yarn test`, `yarn build` pass
- [ ] Order-edit page initial render uses contract-definition superset
- [ ] Switching carrier / packageType / options re-fires action, helpers react
- [ ] Settings page is unaffected
- [ ] Empty `cc` (admin-created orders) doesn't break — helpers fall back to Dynamic
- [ ] Window focus does NOT trigger a capabilities refetch
- [ ] Order-form events do NOT trigger a Dynamic refetch
- [ ] Existing frontend caller of `proxyCapabilities` still works (delivery-options widget) — `options` are now allowlist-filtered server-side

Refs: INT-1505 (sub-task of INT-930)
```

- [ ] **Step 4: Cross-link**

Add "Pairs with: js-pdk PR #N" to PDK PR #453 and the inverse on the JS-PDK PR. Tag QA.

---

## Rollback plan

- **Only PDK merges:** Allowlist filter applies to all `proxyCapabilities` callers (admin + existing frontend). Existing frontend caller sees the filtered shape — verify the delivery-options widget still works (test #7 in the matrix).
- **Only JS-PDK merges:** JS calls the existing `proxyCapabilities` action (already on `v4-capabilities` via the merged proxy branch). Without the allowlist filter, admin would see option keys not backed by definitions. Cosmetic but possibly confusing — prefer to land both.
- **Both merge:** Full functionality.

---

## Out of scope

- Monday delivery capability research ([INT-1506](https://myparcelnl.atlassian.net/browse/INT-1506))
- Delivery-options widget logic (handled in Plan 1 #449)
- Persistent capability cache (request-scope only via existing repository cache; the `CapabilitiesAction` from `feat/add-capabilities-proxy` calls the SDK directly without it — separate follow-up if needed)
- `OrderDataContext` / `inheritedDeliveryOptions` changes
- Auto-generated TS types from PHP

---

## Self-review checklist

- [ ] No new PHP action class. Reused `CapabilitiesAction` from `feat/add-capabilities-proxy`.
- [ ] No new PHP model wrapping `RefCapabilitiesResponseCapabilityV2`. Action returns SDK's natural JSON shape.
- [ ] No new PHP Context.
- [ ] `Carrier::filterRegisteredOptions` is the single source of truth for the option allowlist on both paths (`Carrier::attributesToArray` for contract-definitions, `CapabilitiesAction` for capabilities responses).
- [ ] The allowlist filter in `CapabilitiesAction` is **opt-in** via the body flag `filterOptions: true`. Default behavior (flag absent or false) is an unchanged 1:1 SDK passthrough.
- [ ] The `filterOptions` flag is stripped from the payload before forwarding to `CapabilitiesService::getCapabilities()` so the SDK doesn't receive an unknown arg.
- [ ] Existing frontend caller of `proxyCapabilities` (delivery-options widget) still works unchanged — it doesn't send `filterOptions`, so it receives the full SDK response as before.
- [ ] `feat/add-capabilities-proxy` (or its merge into `v4-capabilities`) is in the base before Phase A starts.
- [ ] No `if ($carrier->carrier === '...')` branches.
- [ ] PHPStan: no NEW errors in modified files.
- [ ] Both PRs opened and cross-linked.
- [ ] Empty `cc` / pre-fetch states don't break the form (helpers fall back to DynamicContext).
- [ ] No order-form event triggers `invalidateQueries` against `[BackendEndpoint.FetchContext, AdminContextKey.Dynamic]`.
- [ ] `useProxyCapabilitiesQuery` has `refetchOnWindowFocus: false`, `refetchOnMount: false`, `refetchOnReconnect: false`.
- [ ] Existing frontend caller of `proxyCapabilities` still works — manual smoke against the delivery-options widget passes (option set may shrink to registered keys, which is desired).
- [ ] Snapshot tests on the PHP side still pass.
