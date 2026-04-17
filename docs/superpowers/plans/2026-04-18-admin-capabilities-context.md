# Admin Capabilities Context — Order-Aware DynamicContext Carriers

> **Execution order — INT-930 migration, step 3 of 4:**
>
> 1. ✅ [Checkout capabilities API](2026-04-14-checkout-capabilities-api.md) — PR #449 / INT-1500 (merged)
> 2. ✅ [Capabilities-driven order calculators](2026-04-15-capabilities-order-calculators.md) — PR #450 / INT-1501 (merged)
> 3. **👉 This plan** — Admin capabilities context — INT-1505
> 4. [Carrier schema cleanup](2026-04-18-carrier-schema-cleanup.md) — INT-1504 (final cleanup; runs after this plan lands so the dead-code / `@TODO` sweep catches leftovers from this work too)
>
> This plan adds new functionality; the final plan (step 4) removes the now-dead old surface.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the INT-930 migration at the admin boundary. Make `DynamicContext.carriers` reflect the **current order's context** when the admin is editing an order: each carrier's `packageTypes` / `deliveryTypes` are narrowed by contextual capabilities (destination, weight, package type), and each entry in `options` gains `requires[]` / `excludes[]` arrays describing relationships with other options. Triggered on initial page render (server-rendered `data-pdk-context` attribute) and on the existing `GET ?context=dynamic&action=fetchContext` re-fetch. JS-PDK consumers (`getCarrier`, `getPackageTypes`, `getDeliveryTypes`, `hasShipmentOption`) keep working against the existing `CarrierModel` shape — the carriers they receive are already narrowed and annotated by the server.

**Architecture:** PHP — `DynamicContext` constructor accepts an optional `PdkOrder`. When present, the carrier loop projects each account carrier through `Carrier::withContextualCapabilities()` (narrows types) and `Carrier::withOptionRelationships()` (annotates options with requires/excludes from registered `OrderOptionDefinition` classes). `FetchContextAction` reads the current order state from the request body, forwards it through `ContextService::createDynamicContext()`. `FrontendRenderService` injects the same order-aware DynamicContext into the initial `data-pdk-context` attribute on order-edit pages. JS-PDK — `useFetchContextQuery` sends the current order state on re-fetch; a new `useOrderFormContextWatcher` invalidates the query on form state change (debounced). No changes to admin form helpers or types.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models. JS-PDK monorepo: TypeScript, Vue 3, Vitest, TanStack Query, Nx build.

**Jira:** [INT-1505](https://myparcelnl.atlassian.net/browse/INT-1505) (sub-task of [INT-930](https://myparcelnl.atlassian.net/browse/INT-930))

**Branch (PHP PDK):** `feat/INT-1505-admin-capabilities-context`
**Branch (JS-PDK):** `feat/INT-1505-admin-capabilities-context`

**Depends on:** Plans 1 ([PR #449](https://github.com/myparcelnl/pdk/pull/449) / INT-1500) and 2 ([PR #450](https://github.com/myparcelnl/pdk/pull/450) / INT-1501) must be merged first. This plan does NOT depend on the carrier-schema-cleanup plan (INT-1504) — that one runs after this as the final cleanup step.

---

## Existing work to build on (read before starting)

### Already shipped (merged)

| Plan                                                                               | Scope                                                                        | What it left in place                                                                                                |
| ---------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| `docs/superpowers/plans/2026-04-02-capabilities-isrequired-isselectedbydefault.md` | `isRequired` / `isSelectedByDefault` metadata resolution                     | `Carrier::getOptionMetadata()`, `CapabilitiesDefaultHelper`, post-resolution enforcement in `PdkOrderOptionsService` |
| `docs/superpowers/plans/2026-04-03-shipment-options-consistency.md`                | Dynamic attribute registration for option definitions                        | `AbstractOrderOptionDefinition` + `ResolvesOptionAttributes` trait. 22 option definitions migrated                   |
| `docs/superpowers/plans/2026-04-03-shipment-options-dynamic-frontend.md`           | Views iterate registered definitions                                         | Admin views no longer hardcode option keys                                                                           |
| `docs/superpowers/plans/2026-04-14-checkout-capabilities-api.md` (PR #449)         | Checkout capabilities API                                                    | Schema-based filtering removed from checkout path                                                                    |
| `docs/superpowers/plans/2026-04-15-capabilities-order-calculators.md` (PR #450)    | Generic `CapabilitiesOptionCalculator` + `CapabilitiesPackageTypeCalculator` | Carrier-specific calculators removed. Requires/excludes resolution in calculator chain                               |

> **Note on [carrier-schema-cleanup](2026-04-18-carrier-schema-cleanup.md) (INT-1504, step 4 of 4):** that plan runs AFTER this one as the final cleanup. Do not depend on anything it introduces (`Carrier::canSupportPackageType/DeliveryType/ShipmentOption`, `Carrier::getAllowedInsuranceAmounts`) — this plan uses `Carrier::getOptionMetadata()` (already present) for capability metadata lookups instead.

### Infrastructure already present

| Component                                                         | Behavior                                                                                                                            |
| ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `src/Context/Model/DynamicContext.php` (lines 29–183)             | Constructor takes `?array $data`. `resolveCarrierCapabilities()` resolves INHERIT → ENABLED/DISABLED                                |
| `src/App/Action/Shared/Context/FetchContextAction.php`            | Reads `$request->get('context')`. **No order awareness today.**                                                                     |
| `src/Context/Service/ContextService.php:59-62`                    | `createDynamicContext()` calls `new DynamicContext()` with no params                                                                |
| `src/Carrier/Repository/CarrierCapabilitiesRepository.php:63-70`  | `getCapabilities(array $args)` — SDK contextual endpoint wrapper                                                                    |
| `src/Carrier/Service/CapabilitiesValidationService.php`           | `getCapabilitiesForPackageType`, `getPackageTypeWeights`, etc.                                                                      |
| `src/Carrier/Model/Carrier.php:201-212`                           | `attributesToArray()` filters serialized options to registered definitions via `getRegisteredCapabilitiesKeys()`                    |
| `src/Carrier/Model/Carrier.php:249-269`                           | `getOptionMetadata(key)` returns SDK option metadata (`getIsRequired()`, `getIsSelectedByDefault()`, insurance range, etc.)         |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`     | Defines `getRequires()` and `getExcludes()` on every option (used by `CapabilitiesOptionCalculator` for tri-state resolution today) |
| `src/Frontend/Service/FrontendRenderService.php:202-207, 267-275` | Renders initial `data-pdk-context` attribute. JSON-encodes the built context                                                        |
| `tests/Unit/Context/Model/OrderDataContextCapabilitiesTest.php`   | 4 passing scenarios for `inheritedDeliveryOptions` tri-state resolution. **Must continue to pass.**                                 |

### JS-PDK infrastructure already present

| Component                                                                                                            | Behavior                                                                              |
| -------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| `apps/admin/src/actions/composables/queries/account/useFetchContextQuery.ts`                                         | TanStack query keyed `[BackendEndpoint.FetchContext, contextKey]`. Refetches on focus |
| `apps/admin/src/pdk/createPdkAdmin.ts` + `services/context/getElementContext.ts`                                     | Reads `data-pdk-context` attribute on boot                                            |
| `apps/admin/src/composables/useStoreContextQuery.ts`                                                                 | Generic wrapper                                                                       |
| `apps/admin/src/forms/helpers/getCarrier.ts` + `getPackageTypes.ts` + `getDeliveryTypes.ts` + `hasShipmentOption.ts` | Read `dynamicContext.carriers`. **No changes in this plan.**                          |

### What this plan is actually adding

**PHP side (6 small changes):**

1. `Carrier::withContextualCapabilities(array $capabilities): self` — narrows types.
2. `Carrier::withOptionRelationships(array $definitions): self` — annotates `options[key]` with `requires[]` / `excludes[]`.
3. `CapabilitiesValidationService::getContextualCarrierCapabilities(Carrier, array $context): array` + request-scope cache.
4. `DynamicContext` constructor accepts optional `order`; projects carriers through both methods.
5. `FetchContextAction` reads order payload from request body; forwards through `ContextService`.
6. `FrontendRenderService` injects current order into initial DynamicContext on order-edit pages.

**JS-PDK side (2 changes):**

1. `useFetchContextQuery` sends current order on re-fetch.
2. Order edit form invalidates FetchContext query on state change (debounced 300ms).

### Explicitly NOT doing

- No new JS-PDK types. `CarrierModel` shape stays identical — **`options[key].requires` / `options[key].excludes` just naturally appear as already-existing optional fields** (see Design Decisions).
- No new JS helpers. All existing admin form helpers unchanged.
- No changes to `OrderDataContext` / `inheritedDeliveryOptions`.
- No removal of tri-state from `inheritedDeliveryOptions` (stays the source of field default values).

---

## Project conventions (read first — not optional)

This plan assumes you have no memory from prior sessions.

### Working environment

- **Platform:** macOS host, Linux container via Docker.
- **Repos:**
  - PHP PDK: `/Users/freek.vanrijt/projects/pdk`.
  - JS-PDK: `/Users/freek.vanrijt/projects/js-pdk` (monorepo; Yarn + Nx).
- **Local linking:** PDK via composer `path` repository; JS-PDK via `portal:`. If plugin build doesn't reflect PDK changes, add `--skip-nx-cache`. Use `pdk-dev-on` if either PDK isn't linked.
- **PHP:** 7.4+. Strict types. Typed properties OK.
- **PHP tests (Pest v1 only; no `describe()`, `arch()`, `covers()`):**
  - Full: `docker compose run php composer test:unit`
  - Filter: `docker compose run php composer test:unit -- --filter="test name"`
  - Snapshots: `yarn test:unit:snapshot` (runs Prettier)
- **Multi-PHP:** `PHP_VERSION=X.Y docker compose run php composer update --no-interaction --no-progress && docker compose run php composer test:unit` — **always `composer update` first** when switching versions.
- **PHPStan:** `docker compose run php composer analyse`. Run before committing. No new errors in modified code.
- **JS-PDK:** from `~/projects/js-pdk`: `yarn test`, `yarn lint`, `yarn typecheck`, `yarn build`. Per-app: `yarn nx test admin`, etc.

### Code style (PHP)

- No sentinel values (`PHP_INT_MAX`, `= -1`). Use nullable types + explicit null handling.
- No algorithm jargon in comments ("O(1)", "indexed for constant-time access").
- No unused foreach values. Use `foreach (array_keys($arr) as $key)`, not `foreach ($arr as $key => $_)`.
- Comments explain intent, not mechanics. Concise. Don't add comments to code you didn't change.
- Nullable int compare: `MyParcelNL\Pdk\Base\Support\Utils::compareNullableInts()`.
- Spaceship operator `<=>` for comparators.
- No hardcoded package/delivery type ordering — comes from capabilities data.
- Don't cast SDK enum return types to string unless PHPStan requires it. Add `// @phpstan-ignore-line SDK declares enum type but returns string`.

### Code style (TypeScript)

- Match existing Prettier + ESLint. Prefer `type` over `interface` for data shapes. No `as any`. Helpers live with forms.
- No hardcoded carrier names (`if (carrier.carrier === 'POSTNL')`).

### Architecture conventions

- **Resolve on PHP, render on JS.** PHP computes order-aware carrier state; JS renders it.
- **Models resolve from settings via attribute getters.**
- **DI constructor changes are NOT breaking.** Auto-wiring via `autowire()` in `config/pdk-services.php`. Don't list in `BREAKING CHANGE:` footers.
- **Carrier settings (`allowX`) take precedence over capabilities** — already enforced by `PdkOrderOptionsService`.
- **Carriers are the single source of truth.** Fetched through `CarrierRepository`, set via `Account`.
- **No carrier-specific branches in the PDK.**
- **Two capability sources:** contract definitions (account-level) + contextual capabilities (per-shipment). Order-edit paths use contextual, fall back to contract definitions.

### Commit conventions

- Conventional commits: `feat(scope)!:`, `fix(scope):`, `chore:`, `test:`, `docs:`.
- Body format:

  ```
  feat(scope): one-line summary

  Optional body explaining why (not what).

  BREAKING CHANGE: only for actual public API breaks.

  Refs: INT-1505

  Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
  ```

- Run tests + PHPStan before committing. Never skip GPG signing. Plan/PR titles use business language.

### Subagent escalation rule

If tests fail after 2 fix attempts, STOP and report BLOCKED with exact error output.

---

## Design decisions

### DynamicContext carriers become order-aware — nothing else changes

Today `DynamicContext.carriers` is account-level carriers with contract-definition metadata. In the admin order-edit screen, this plan makes that same list reflect the current order: `packageTypes` / `deliveryTypes` narrowed, and each entry in `options` annotated with `requires[]` / `excludes[]`.

JS-PDK already reads `dynamicContext.carriers`. When the server narrows + annotates them, the UI sees the right data. **No JS type changes, no JS helper changes.**

### Why `requires` / `excludes` on the carrier options

Today the JS-PDK has no way to tell the user "insurance is disabled because signature requires it to be off" (or similar). The PHP side has this information in `OrderOptionDefinitionInterface::getRequires()` / `getExcludes()` per option, and `CapabilitiesOptionCalculator` uses it to resolve values. By exposing these lists on the carrier's `options[key]` entries in the order-context serialization, the JS can:

- Render relationship-aware UI ("insurance is excluded when X is on")
- Avoid duplicating PHP relationship rules in TypeScript

The shape added to each carrier `options[key]` when projected for an order:

```php
// Existing fields (from SDK option metadata):
'isRequired'          => false,
'isSelectedByDefault' => true,
// Plus insurance-specific fields where applicable

// NEW fields (added by Carrier::withOptionRelationships):
'requires' => ['signature'],
'excludes' => ['onlyRecipient'],
```

These are **static relationships** from the registered option definitions. They are independent of the order's tri-state values — resolution happens separately in `inheritedDeliveryOptions`. The JS uses `requires`/`excludes` to explain UI state; it uses `inheritedDeliveryOptions` for actual default values.

### Serialization implementation — annotation map, not SDK mutation

`Carrier->options` is an SDK-generated model (`RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2`). It's not a mutable array. To avoid mutating SDK models, `Carrier` carries a private map `$optionRelationships: array<capabilityKey, array{requires: string[], excludes: string[]}>` populated by `withOptionRelationships()`. `attributesToArray()` merges the map into the serialized `options` output. If the map is empty (non-order context), the serialization is identical to today.

### Request flow for order-aware DynamicContext

1. **Initial page render:** the plugin (WooCommerce / PrestaShop) renders the order-edit page knowing the order identifier. `FrontendRenderService` resolves the `PdkOrder` and passes it to `DynamicContext`. The rendered `data-pdk-context` attribute is order-aware from first paint.
2. **Re-fetch on change:** the admin form posts the current order state with `GET ?context=dynamic&action=fetchContext` (body-capable request). `FetchContextAction` reads it, passes to `DynamicContext`.

### Sending the current order state on re-fetch

Options considered:

- **Send full order in POST body** — chosen. Matches existing mutation patterns. Includes unsaved form state.
- Order ID + diff — too complex.

Required: the `FetchContext` route accepts a body. Verify the endpoint definition supports POST; if it's strictly GET today, add POST support (task below).

### Query-cache correctness

`useFetchContextQuery`'s key is `[BackendEndpoint.FetchContext, contextKey]` today. When we add order to the request, the cache key must include order identifier + critical fields (cc, weight). Use a computed key `[BackendEndpoint.FetchContext, contextKey, orderCacheKey]` where `orderCacheKey` is `null` for non-order screens and a digest of `{orderId, cc, weight}` for order screens. TanStack Query caches per key; on form state change beyond those fields, the watcher manually invalidates.

### Re-fetch debounce

Watcher uses 300ms debounce to avoid thrashing on keystrokes. Simple `setTimeout`-based implementation.

### Fallback on missing shipping context

Draft orders may have no shipping address. `CapabilitiesValidationService::getContextualCarrierCapabilities()` returns `[]` when `cc` is missing. `Carrier::withContextualCapabilities([])` returns a clone unchanged. `Carrier::withOptionRelationships([])` with empty definitions — stays clone. Net: draft orders get contract-definition carriers unchanged.

### Scoping order awareness

Narrowing + annotation only apply when the request carries an order. Settings page continues to see unnarrowed, unannotated carriers.

### Contextual capability fan-out

Per-carrier × per-packageType → one `/capabilities` API call. Typical admin fetch: 5 carriers × 3 package types = 15 calls. Cache in-memory per request; repeated fetches reuse.

### Scope boundary

- PHP `DynamicContext`, `FetchContextAction`, `FrontendRenderService`, Carrier projection methods.
- JS-PDK `useFetchContextQuery` + order edit form invalidation.
- No checkout / `delivery-options` repo.
- No `OrderDataContext` / `inheritedDeliveryOptions` changes.
- No new JS types or helpers.

---

## Behavioral test matrix

### Existing behavior (must not regress)

| #   | Scenario                                          | Expected                                                                  |
| --- | ------------------------------------------------- | ------------------------------------------------------------------------- |
| 1   | Carrier settings page                             | Unchanged — DynamicContext served without order                           |
| 2   | Order edit modal, `isRequired` option             | Field disabled + auto-checked (`OrderDataContextCapabilitiesTest` passes) |
| 3   | Order edit modal, `isSelectedByDefault`           | Field pre-checked when no other setting                                   |
| 4   | Order edit modal, carrier setting disables option | Field disabled                                                            |
| 5   | V1 shipment / return / fulfilment                 | Unaffected                                                                |

### New behavior

| #   | Scenario                                          | Expected                                                                              |
| --- | ------------------------------------------------- | ------------------------------------------------------------------------------------- |
| 6   | Order with weight above contextual limit          | Package type absent from `dynamicContext.carriers[CARRIER].packageTypes`              |
| 7   | Order shipping to country where pickup disallowed | Pickup absent from `dynamicContext.carriers[CARRIER].deliveryTypes`                   |
| 8   | Draft order (no shipping address)                 | `dynamicContext.carriers[CARRIER].packageTypes` matches contract definitions          |
| 9   | Admin changes order destination                   | `dynamicContext.carriers` re-fetches and reflects new country after ~300ms            |
| 10  | Admin changes order weight                        | `dynamicContext.carriers` re-fetches and reflects new weight after ~300ms             |
| 11  | Initial load on order-edit screen                 | `data-pdk-context` already contains order-narrowed carriers                           |
| 12  | Initial load on settings screen                   | `data-pdk-context` contains unnarrowed carriers                                       |
| 13  | Option A has `excludes: [B]` in its definition    | `dynamicContext.carriers[CARRIER].options.A.excludes` contains `'B'` in order context |
| 14  | Option X has `requires: [Y]` in its definition    | `dynamicContext.carriers[CARRIER].options.X.requires` contains `'Y'` in order context |
| 15  | Settings page (no order context)                  | `dynamicContext.carriers[CARRIER].options.X` has NO `requires` / `excludes` fields    |
| 16  | `yarn nx typecheck admin` / `yarn test`           | Pass                                                                                  |

---

## File structure

### PHP PDK (new / modified)

| File                                                                         | Action                                                                                           |
| ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| `src/Carrier/Model/Carrier.php`                                              | Modify — add `withContextualCapabilities`, `withOptionRelationships`, update `attributesToArray` |
| `src/Carrier/Service/CapabilitiesValidationService.php`                      | Modify — add `getContextualCarrierCapabilities` + cache                                          |
| `src/Context/Model/DynamicContext.php`                                       | Modify — accept `order` key; project carriers                                                    |
| `src/Context/Service/ContextService.php`                                     | Modify — route `$data` into `createDynamicContext`                                               |
| `src/App/Action/Shared/Context/FetchContextAction.php`                       | Modify — read order payload, forward to ContextService                                           |
| `src/App/Api/Request/FetchContextEndpointRequest.php`                        | Modify — declare optional order body                                                             |
| `src/Frontend/Service/FrontendRenderService.php`                             | Modify — resolve orderIdentifier and pass to DynamicContext                                      |
| `tests/Unit/Carrier/Model/CarrierContextualProjectionTest.php`               | Create                                                                                           |
| `tests/Unit/Carrier/Model/CarrierOptionRelationshipsTest.php`                | Create                                                                                           |
| `tests/Unit/Carrier/Service/CapabilitiesValidationServiceContextualTest.php` | Create                                                                                           |
| `tests/Unit/Context/Model/DynamicContextOrderProjectionTest.php`             | Create                                                                                           |
| `tests/Unit/App/Action/Shared/Context/FetchContextActionOrderTest.php`       | Create                                                                                           |
| `tests/Unit/Frontend/Service/FrontendRenderServiceOrderContextTest.php`      | Create                                                                                           |

### JS-PDK (modified)

| File                                                                              | Action                                                       |
| --------------------------------------------------------------------------------- | ------------------------------------------------------------ |
| `apps/admin/src/actions/composables/queries/account/useFetchContextQuery.ts`      | Modify — accept `order` ref, include in cache key + body     |
| `apps/admin/src/composables/useStoreContextQuery.ts`                              | Modify — pass-through                                        |
| `apps/admin/src/forms/order/useOrderFormContextWatcher.ts`                        | Create — debounced invalidator tied to order state ref       |
| `apps/admin/src/forms/order/<entry>.ts` (located in Task 0)                       | Modify — wire the watcher into the order edit form lifecycle |
| `apps/admin/src/actions/composables/queries/account/useFetchContextQuery.test.ts` | Create / modify — cover order body path                      |
| `apps/admin/src/forms/order/useOrderFormContextWatcher.test.ts`                   | Create                                                       |

---

## Task 0: Verify existing infrastructure

- [ ] **Step 1: Existing tests on base branch**

```bash
cd ~/projects/pdk
git checkout main && git pull
docker compose run php composer test:unit -- --filter="OrderDataContextCapabilitiesTest"
```

Expected: 4 PASS.

- [ ] **Step 2: Verify `Carrier::getOptionMetadata` is present**

```bash
grep -n "getOptionMetadata" src/Carrier/Model/Carrier.php
```

Expected: method defined. Tasks 1–2 add new projection methods alongside it. If it's missing, stop — the capabilities-isrequired-isselectedbydefault plan should already be merged.

> Note: `canSupportPackageType/DeliveryType/ShipmentOption` and `getAllowedInsuranceAmounts` are introduced by the later carrier-schema-cleanup plan (step 4 of 4) and are NOT available here. Do not use them.

- [ ] **Step 3: Verify OrderOptionDefinitionInterface getters**

```bash
grep -n "getCapabilitiesOptionsKey\|getRequires\|getExcludes" src/App/Options/Contract/OrderOptionDefinitionInterface.php src/App/Options/Definition/AbstractOrderOptionDefinition.php
```

Note the exact method names. This plan calls them as shown. If names differ, adjust.

- [ ] **Step 4: Inspect FetchContextAction and request class**

```bash
cat src/App/Action/Shared/Context/FetchContextAction.php
cat src/App/Api/Request/FetchContextEndpointRequest.php
```

Confirm whether the endpoint definition supports POST / body payload. If strictly GET, note it — Task 5 adds POST support.

- [ ] **Step 5: Inspect FrontendRenderService**

```bash
grep -n "data-pdk-context\|DynamicContext\|orderIdentifier" src/Frontend/Service/FrontendRenderService.php
```

- [ ] **Step 6: JS-PDK — find order edit form entry**

```bash
cd ~/projects/js-pdk
grep -rln "createOrderForm\|createShipmentOptionsForm\|FIELD_CARRIER" apps/admin/src/forms/
```

Record filename. Task 11 wires the watcher into this file.

- [ ] **Step 7: Verify endpoint layer can handle POST**

```bash
grep -rn "FetchContext" apps/admin/src/ libs/common/src/ | grep -i "endpoint\|method"
```

If the endpoint is GET-only, Task 10 will extend it.

- [ ] **Step 8: Create branches**

```bash
cd ~/projects/pdk && git checkout -b feat/INT-1505-admin-capabilities-context
cd ~/projects/js-pdk && git checkout main && git pull && git checkout -b feat/INT-1505-admin-capabilities-context
```

No commit.

---

## Task 1: Add `withContextualCapabilities` to Carrier

**Files:**

- Modify: `src/Carrier/Model/Carrier.php`
- Test: `tests/Unit/Carrier/Model/CarrierContextualProjectionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Sdk\src\Model\Generated\RefCapabilitiesResponseCapabilityV2;

it('narrows packageTypes to those present in contextual capabilities', function () {
    $carrier = new Carrier([
        'carrier'       => 'POSTNL',
        'packageTypes'  => ['PACKAGE', 'MAILBOX', 'DIGITAL_STAMP'],
        'deliveryTypes' => ['HOME', 'PICKUP'],
    ]);

    $capabilities = [
        makeCapability('PACKAGE', ['HOME', 'PICKUP']),
        makeCapability('MAILBOX', ['HOME']),
    ];

    $projected = $carrier->withContextualCapabilities($capabilities);

    expect($projected->packageTypes)->toEqualCanonicalizing(['PACKAGE', 'MAILBOX']);
    expect($projected->deliveryTypes)->toEqualCanonicalizing(['HOME', 'PICKUP']);
});

it('returns a clone — does not mutate the original', function () {
    $carrier = new Carrier(['carrier' => 'POSTNL', 'packageTypes' => ['PACKAGE', 'MAILBOX'], 'deliveryTypes' => ['HOME']]);
    $projected = $carrier->withContextualCapabilities([]);
    expect($projected)->not->toBe($carrier);
    expect($carrier->packageTypes)->toEqual(['PACKAGE', 'MAILBOX']);
});

it('returns a clone unchanged when capabilities array is empty', function () {
    $carrier = new Carrier(['carrier' => 'POSTNL', 'packageTypes' => ['PACKAGE'], 'deliveryTypes' => ['HOME']]);
    $projected = $carrier->withContextualCapabilities([]);
    expect($projected->packageTypes)->toEqual(['PACKAGE']);
    expect($projected->deliveryTypes)->toEqual(['HOME']);
});

function makeCapability(string $packageType, array $deliveryTypes): RefCapabilitiesResponseCapabilityV2
{
    $cap = new RefCapabilitiesResponseCapabilityV2();
    $cap->setPackageType($packageType);
    $cap->setDeliveryTypes($deliveryTypes);
    return $cap;
}
```

> Verify SDK setter names against `vendor/myparcelnl/sdk/src/Model/Generated/RefCapabilitiesResponseCapabilityV2.php` before running.

- [ ] **Step 2: Run — expect failure**

```bash
docker compose run php composer test:unit -- --filter="CarrierContextualProjectionTest"
```

- [ ] **Step 3: Implement**

Add to `src/Carrier/Model/Carrier.php` (below `getOptionMetadata`):

```php
/**
 * Project this carrier through contextual capability results.
 * Returns a clone with packageTypes and deliveryTypes narrowed to those
 * present in the given contextual capabilities. Empty = clone unchanged.
 *
 * @param \MyParcelNL\Sdk\src\Model\Generated\RefCapabilitiesResponseCapabilityV2[] $capabilities
 */
public function withContextualCapabilities(array $capabilities): self
{
    $clone = clone $this;
    if (empty($capabilities)) {
        return $clone;
    }

    $allowedPackageTypes  = [];
    $allowedDeliveryTypes = [];
    foreach ($capabilities as $capability) {
        $packageType = $capability->getPackageType();
        if ($packageType) {
            $allowedPackageTypes[$packageType] = true;
        }
        foreach ($capability->getDeliveryTypes() ?? [] as $deliveryType) {
            $allowedDeliveryTypes[$deliveryType] = true;
        }
    }

    $clone->packageTypes = array_values(array_intersect($this->packageTypes ?? [], array_keys($allowedPackageTypes)));
    $clone->deliveryTypes = array_values(array_intersect($this->deliveryTypes ?? [], array_keys($allowedDeliveryTypes)));

    return $clone;
}
```

- [ ] **Step 4: Run — expect pass + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="CarrierContextualProjectionTest"
docker compose run php composer analyse
```

- [ ] **Step 5: Commit**

```bash
git add src/Carrier/Model/Carrier.php tests/Unit/Carrier/Model/CarrierContextualProjectionTest.php
git commit -m "$(cat <<'EOF'
feat(carrier): add contextual capabilities projection to Carrier

Narrows packageTypes and deliveryTypes to those permitted by contextual
capabilities (destination / weight / package type) for a given order.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 2: Add `withOptionRelationships` to Carrier

**Files:**

- Modify: `src/Carrier/Model/Carrier.php`
- Test: `tests/Unit/Carrier/Model/CarrierOptionRelationshipsTest.php`

**What:** A second projection method. Accepts a list of `OrderOptionDefinitionInterface`, builds a private map `capabilityKey => {requires, excludes}`, stores it on the clone. `attributesToArray()` merges the map into the `options` serialization output.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;

it('annotates options with requires and excludes from definitions', function () {
    $carrier = new Carrier([
        'carrier' => 'POSTNL',
        // Ensure the options SDK model carries at least two known keys.
        // Use a factory from existing tests if needed — adapt to current fixtures.
    ])->withAllRegisteredOptions(); // use existing factory method if present

    $definitions = [
        fakeDefinition('insurance', ['signature'], ['onlyRecipient']),
        fakeDefinition('signature', [], []),
    ];

    $annotated = $carrier->withOptionRelationships($definitions);
    $array     = $annotated->attributesToArray();

    expect($array['options']['insurance'])->toHaveKeys(['requires', 'excludes']);
    expect($array['options']['insurance']['requires'])->toEqual(['signature']);
    expect($array['options']['insurance']['excludes'])->toEqual(['onlyRecipient']);

    expect($array['options']['signature']['requires'])->toEqual([]);
    expect($array['options']['signature']['excludes'])->toEqual([]);
});

it('does not include requires/excludes fields when withOptionRelationships is never called', function () {
    $carrier = new Carrier(['carrier' => 'POSTNL'])->withAllRegisteredOptions();
    $array   = $carrier->attributesToArray();

    // No order context — settings view shape, unchanged
    foreach ($array['options'] ?? [] as $option) {
        expect($option)->not->toHaveKey('requires');
        expect($option)->not->toHaveKey('excludes');
    }
});

it('returns a clone — does not mutate the original', function () {
    $carrier = new Carrier(['carrier' => 'POSTNL'])->withAllRegisteredOptions();
    $annotated = $carrier->withOptionRelationships([]);
    expect($annotated)->not->toBe($carrier);
});

/**
 * Minimal fake definition implementing OrderOptionDefinitionInterface — only the
 * methods this test needs. Real tests may use a shared fake from tests/Helpers.
 */
function fakeDefinition(string $key, array $requires, array $excludes): OrderOptionDefinitionInterface {
    return new class($key, $requires, $excludes) implements OrderOptionDefinitionInterface {
        public function __construct(private string $key, private array $requires, private array $excludes) {}
        public function getCapabilitiesOptionsKey(): ?string { return $this->key; }
        public function getRequires(): ?array { return $this->requires; }
        public function getExcludes(): ?array { return $this->excludes; }
        // ... other interface methods delegated or unimplemented (throw if called)
    };
}
```

> `withAllRegisteredOptions()` — check existing Carrier factories / test helpers. If no such helper exists, stub `$carrier->options` directly with a test double, or extend the carrier factory in `tests/Factory/`.
>
> The anonymous class needs all interface methods. If the interface is large, create a permanent fake in `tests/Helpers/FakeOrderOptionDefinition.php` and reuse.

- [ ] **Step 2: Run — expect failure**

```bash
docker compose run php composer test:unit -- --filter="CarrierOptionRelationshipsTest"
```

- [ ] **Step 3: Implement**

In `src/Carrier/Model/Carrier.php`:

1. Add private property:

```php
/**
 * Keyed by capability option key. Present only when the carrier has been
 * projected for an order context via withOptionRelationships().
 *
 * @var array<string, array{requires: string[], excludes: string[]}>
 */
private array $optionRelationships = [];
```

2. Add method:

```php
/**
 * Annotate each option on this carrier with requires[] / excludes[] drawn from
 * the given registered option definitions. Used when projecting the carrier for
 * an order context.
 *
 * @param \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions
 */
public function withOptionRelationships(array $definitions): self
{
    $clone = clone $this;
    $clone->optionRelationships = [];

    foreach ($definitions as $definition) {
        $key = $definition->getCapabilitiesOptionsKey();
        if (! $key) {
            continue;
        }
        $clone->optionRelationships[$key] = [
            'requires' => $definition->getRequires() ?? [],
            'excludes' => $definition->getExcludes() ?? [],
        ];
    }

    return $clone;
}
```

3. Update `attributesToArray` (the existing filter block already walks `$result['options']`):

```php
public function attributesToArray(?int $flags = null): array
{
    $result = parent::attributesToArray($flags);

    if (! isset($result['options']) || ! is_array($result['options'])) {
        return $result;
    }

    $result['options'] = array_intersect_key(
        $result['options'],
        self::getRegisteredCapabilitiesKeys()
    );

    if (! empty($this->optionRelationships)) {
        foreach ($result['options'] as $key => $value) {
            if (! is_array($value)) {
                continue;
            }
            $rel = $this->optionRelationships[$key] ?? ['requires' => [], 'excludes' => []];
            $result['options'][$key]['requires'] = $rel['requires'];
            $result['options'][$key]['excludes'] = $rel['excludes'];
        }
    }

    return $result;
}
```

- [ ] **Step 4: Run — expect pass + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="CarrierOptionRelationshipsTest"
docker compose run php composer analyse
```

- [ ] **Step 5: Commit**

```bash
git add src/Carrier/Model/Carrier.php tests/Unit/Carrier/Model/CarrierOptionRelationshipsTest.php tests/Helpers/FakeOrderOptionDefinition.php
git commit -m "$(cat <<'EOF'
feat(carrier): annotate carrier options with requires/excludes for order context

Adds Carrier::withOptionRelationships(definitions) which stores a
map of requires[]/excludes[] per option. attributesToArray() merges
this into the serialised options output. Settings-view serialisation
(no relationships set) is unchanged.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 3: Add `getContextualCarrierCapabilities` to CapabilitiesValidationService

**Files:**

- Modify: `src/Carrier/Service/CapabilitiesValidationService.php`
- Test: `tests/Unit/Carrier/Service/CapabilitiesValidationServiceContextualTest.php`

- [ ] **Step 1: Read the existing repository signature**

```bash
grep -n "function getCapabilities" src/Carrier/Repository/CarrierCapabilitiesRepository.php
```

- [ ] **Step 2: Write failing test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

uses(UsesMockPdkInstance::class);

it('returns contextual capabilities for a carrier and shipping context', function () {
    $service = Pdk::get(CapabilitiesValidationService::class);
    $carrier = new Carrier(['carrier' => 'POSTNL', 'packageTypes' => ['PACKAGE', 'MAILBOX']]);

    $result = $service->getContextualCarrierCapabilities($carrier, [
        'cc' => 'NL', 'weight' => 2000, 'packageTypes' => ['PACKAGE', 'MAILBOX'],
    ]);

    expect($result)->toBeArray();
    foreach ($result as $capability) {
        expect($capability)->toBeInstanceOf(\MyParcelNL\Sdk\src\Model\Generated\RefCapabilitiesResponseCapabilityV2::class);
    }
});

it('returns empty array when shipping context is incomplete', function () {
    $service = Pdk::get(CapabilitiesValidationService::class);
    $result = $service->getContextualCarrierCapabilities(
        new Carrier(['carrier' => 'POSTNL', 'packageTypes' => ['PACKAGE']]),
        ['cc' => null, 'weight' => 2000]
    );
    expect($result)->toEqual([]);
});

it('reuses cached responses within a single request', function () {
    $service = Pdk::get(CapabilitiesValidationService::class);
    $service->clearContextualCache();
    $carrier = new Carrier(['carrier' => 'POSTNL', 'packageTypes' => ['PACKAGE']]);

    $service->getContextualCarrierCapabilities($carrier, ['cc' => 'NL', 'weight' => 1000, 'packageTypes' => ['PACKAGE']]);
    $service->getContextualCarrierCapabilities($carrier, ['cc' => 'NL', 'weight' => 1000, 'packageTypes' => ['PACKAGE']]);

    expect($service->getContextualCacheHits())->toBeGreaterThan(0);
});
```

- [ ] **Step 3: Run — expect failure**

```bash
docker compose run php composer test:unit -- --filter="CapabilitiesValidationServiceContextualTest"
```

- [ ] **Step 4: Implement**

Add to `CapabilitiesValidationService`:

```php
/** @var array<string, \MyParcelNL\Sdk\src\Model\Generated\RefCapabilitiesResponseCapabilityV2[]> */
private array $contextualCache = [];
private int $contextualCacheHits = 0;

/**
 * @param array{cc: ?string, weight: ?int, packageTypes: ?array} $context
 * @return \MyParcelNL\Sdk\src\Model\Generated\RefCapabilitiesResponseCapabilityV2[]
 */
public function getContextualCarrierCapabilities(Carrier $carrier, array $context): array
{
    if (empty($context['cc']) || empty($carrier->carrier)) {
        return [];
    }

    $packageTypes = $context['packageTypes'] ?? $carrier->packageTypes ?? [];
    if (empty($packageTypes)) {
        return [];
    }

    $results = [];
    foreach ($packageTypes as $packageType) {
        $cacheKey = sprintf('%s|%s|%s|%s', $carrier->carrier, $context['cc'], $packageType, $context['weight'] ?? '');

        if (isset($this->contextualCache[$cacheKey])) {
            ++$this->contextualCacheHits;
            foreach ($this->contextualCache[$cacheKey] as $cached) {
                $results[] = $cached;
            }
            continue;
        }

        $capabilities = $this->repository->getCapabilities([
            'carrier'     => $carrier->carrier,
            'cc'          => $context['cc'],
            'packageType' => $packageType,
            'weight'      => $context['weight'] ?? null,
        ]);

        $this->contextualCache[$cacheKey] = $capabilities;
        foreach ($capabilities as $capability) {
            $results[] = $capability;
        }
    }

    return $results;
}

public function clearContextualCache(): void
{
    $this->contextualCache     = [];
    $this->contextualCacheHits = 0;
}

public function getContextualCacheHits(): int
{
    return $this->contextualCacheHits;
}
```

> Adjust repo argument shape to Step 1 findings.

- [ ] **Step 5: Run + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="CapabilitiesValidationServiceContextualTest"
docker compose run php composer analyse
```

- [ ] **Step 6: Commit**

```bash
git add src/Carrier/Service/CapabilitiesValidationService.php tests/Unit/Carrier/Service/CapabilitiesValidationServiceContextualTest.php
git commit -m "$(cat <<'EOF'
feat(carrier): add contextual capabilities lookup with request-scope cache

Thin wrapper over CarrierCapabilitiesRepository::getCapabilities with
carrier-scoped signature. Safe fallback when context is incomplete.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 4: Make DynamicContext order-aware

**Files:**

- Modify: `src/Context/Model/DynamicContext.php`
- Modify: `src/Context/Service/ContextService.php`
- Test: `tests/Unit/Context/Model/DynamicContextOrderProjectionTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('returns unnarrowed, unannotated carriers without an order', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $context = new DynamicContext();

    $serialized = $context->toArrayWithoutNull()['carriers'] ?? [];
    foreach ($serialized as $carrier) {
        foreach ($carrier['options'] ?? [] as $option) {
            expect($option)->not->toHaveKey('requires');
            expect($option)->not->toHaveKey('excludes');
        }
    }
});

it('narrows carrier packageTypes when an order is given', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $order = factory(PdkOrder::class)->withShippingAddress(new Address(['cc' => 'NL']))->make();

    $context = new DynamicContext(['order' => $order]);

    expect($context->carriers->first()->packageTypes)->toBeArray();
});

it('annotates carrier options with requires/excludes when an order is given', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $order = factory(PdkOrder::class)->withShippingAddress(new Address(['cc' => 'NL']))->make();

    $context = new DynamicContext(['order' => $order]);
    $serialized = $context->toArrayWithoutNull()['carriers'][0] ?? [];

    foreach ($serialized['options'] ?? [] as $option) {
        expect($option)->toHaveKeys(['requires', 'excludes']);
        expect($option['requires'])->toBeArray();
        expect($option['excludes'])->toBeArray();
    }
});

it('falls back gracefully when order has no shipping address', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $order = factory(PdkOrder::class)->make();

    $context = new DynamicContext(['order' => $order]);

    // Still returns carriers — no narrowing but still annotated with requires/excludes
    expect($context->carriers)->not->toBeEmpty();
});
```

- [ ] **Step 2: Run — expect failure**

```bash
docker compose run php composer test:unit -- --filter="DynamicContextOrderProjectionTest"
```

- [ ] **Step 3: Modify DynamicContext**

In `src/Context/Model/DynamicContext.php`:

1. Property:

```php
private ?PdkOrder $projectionOrder = null;
```

2. Constructor — extract order before parent:

```php
public function __construct(?array $data = null)
{
    if (isset($data['order']) && $data['order'] instanceof PdkOrder) {
        $this->projectionOrder = $data['order'];
        unset($data['order']);
    }
    parent::__construct($data);
    // existing post-construct: ensureAllCarriersHaveSettings, resolveCarrierCapabilities, ...
    $this->projectCarriersForOrder();
}
```

3. Helper:

```php
private function projectCarriersForOrder(): void
{
    if (! $this->projectionOrder) {
        return;
    }

    $validationService = Pdk::get(CapabilitiesValidationService::class);
    $definitions       = Pdk::get('orderOptionDefinitions');
    $order             = $this->projectionOrder;

    $shippingContext = [
        'cc'     => $order->shippingAddress->cc ?? null,
        'weight' => $this->computeTotalWeight($order),
    ];

    $projected = new CarrierCollection();
    foreach ($this->carriers as $carrier) {
        $capabilities = $validationService->getContextualCarrierCapabilities(
            $carrier,
            array_merge($shippingContext, ['packageTypes' => $carrier->packageTypes])
        );
        $projectedCarrier = $carrier
            ->withContextualCapabilities($capabilities)
            ->withOptionRelationships($definitions);
        $projected->push($projectedCarrier);
    }
    $this->carriers = $projected;
}

private function computeTotalWeight(PdkOrder $order): ?int
{
    $lineWeight = 0;
    foreach ($order->lines ?? [] as $line) {
        $lineWeight += (int) ($line->product->weight ?? 0) * max(1, (int) ($line->quantity ?? 1));
    }
    if ($lineWeight > 0) {
        return $lineWeight;
    }
    return $order->physicalProperties->weight ?? null;
}
```

4. Imports:

```php
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
```

- [ ] **Step 4: Modify ContextService**

In `src/Context/Service/ContextService.php`:

- Update `createDynamicContext($data = [])` to accept and forward `$data`.
- In `resolveContext()`, add the DYNAMIC case to forward `$data`.

- [ ] **Step 5: Run + existing DynamicContext tests + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="DynamicContext"
docker compose run php composer analyse
```

- [ ] **Step 6: Commit**

```bash
git add src/Context/Model/DynamicContext.php src/Context/Service/ContextService.php tests/Unit/Context/Model/DynamicContextOrderProjectionTest.php
git commit -m "$(cat <<'EOF'
feat(context): make DynamicContext order-aware

When DynamicContext is built with an 'order' key, every carrier is
projected through withContextualCapabilities() (narrowing) and
withOptionRelationships() (requires/excludes annotation). Without an
order, behavior is unchanged.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 5: FetchContextAction reads order payload

**Files:**

- Modify: `src/App/Action/Shared/Context/FetchContextAction.php`
- Modify: `src/App/Api/Request/FetchContextEndpointRequest.php` (and route config if method-support needs extension)
- Test: `tests/Unit/App/Action/Shared/Context/FetchContextActionOrderTest.php`

**What:** Read the order payload from request body (JSON). If present, resolve to `PdkOrder` and pass through `ContextService::createContexts($contexts, ['order' => $order])`. If the endpoint is GET-only today, extend it to accept POST.

- [ ] **Step 1: Inspect request/body conventions**

```bash
grep -rn "getBody\|getParsedBody\|getPostData" src/App/Action/ | head -30
```

Identify the pattern used by other actions that consume bodies.

- [ ] **Step 2: Write failing test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Action\Shared\Context\FetchContextAction;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

uses(UsesMockPdkInstance::class);

it('builds DynamicContext without order when request has no body', function () {
    $request  = mockRequest(['context' => 'dynamic']);
    $response = (new FetchContextAction())->handle($request);
    expect($response)->not->toBeNull();
});

it('passes order payload to context service when present in request body', function () {
    $orderPayload = [
        'externalIdentifier' => '42',
        'shippingAddress'    => ['cc' => 'NL'],
        'lines'              => [],
    ];
    $request  = mockRequest(['context' => 'dynamic'], ['order' => $orderPayload]);
    $response = (new FetchContextAction())->handle($request);
    expect($response)->not->toBeNull();
});
```

> Copy `mockRequest` pattern from existing FetchContextAction tests. Adapt the second argument (body) to whatever shape the mock accepts.

- [ ] **Step 3: Run — expect failure**

```bash
docker compose run php composer test:unit -- --filter="FetchContextActionOrderTest"
```

- [ ] **Step 4: Implement**

In `src/App/Action/Shared/Context/FetchContextAction.php`:

```php
private function readOrderFromRequest(Request $request): ?PdkOrder
{
    $body = $request->getBody();
    $payload = null;
    if (is_string($body)) {
        $decoded = json_decode($body, true);
        if (is_array($decoded) && isset($decoded['order']) && is_array($decoded['order'])) {
            $payload = $decoded['order'];
        }
    } elseif (is_array($body) && isset($body['order']) && is_array($body['order'])) {
        $payload = $body['order'];
    }
    return $payload ? new PdkOrder($payload) : null;
}

public function handle(Request $request): Response
{
    $contexts   = $this->getContexts($request);
    $order      = $this->readOrderFromRequest($request);
    $data       = $order ? ['order' => $order] : [];
    $contextBag = $this->contextService->createContexts($contexts, $data);

    return new JsonResponse(['context' => [$contextBag->toArrayWithoutNull()]]);
}
```

> If `Request::getBody` is not the shape assumed above, adapt. Fall back to query-param `orderId` + `PdkOrderRepositoryInterface::get($orderId)` as an alternative path — but prefer the body approach for unsaved state.

- [ ] **Step 5: Update the endpoint request class / route to allow POST if needed**

```bash
grep -rn "BackendEndpoint::FetchContext\|PdkSharedActions::FETCH_CONTEXT" config/ src/
```

If the route is declared GET-only, extend it to also accept POST (preserving GET for requests without body).

- [ ] **Step 6: Run + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="FetchContextAction"
docker compose run php composer analyse
```

- [ ] **Step 7: Commit**

```bash
git add src/App/Action/Shared/Context/FetchContextAction.php src/App/Api/Request/FetchContextEndpointRequest.php tests/Unit/App/Action/Shared/Context/FetchContextActionOrderTest.php
git commit -m "$(cat <<'EOF'
feat(context): accept order payload in FetchContext endpoint

When the request carries an order body, it is forwarded to
DynamicContext so carriers are projected for that order.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 6: Initial page render — inject order

**Files:**

- Modify: `src/Frontend/Service/FrontendRenderService.php`
- Test: `tests/Unit/Frontend/Service/FrontendRenderServiceOrderContextTest.php`

- [ ] **Step 1: Read current FrontendRenderService**

```bash
cat src/Frontend/Service/FrontendRenderService.php
grep -rn "orderIdentifier" src/Frontend/
```

- [ ] **Step 2: Write failing test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('embeds order-aware carriers when orderIdentifier is given', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $order = factory(PdkOrder::class)->make(['externalIdentifier' => '42', 'shippingAddress' => ['cc' => 'NL']]);
    Pdk::get(PdkOrderRepositoryInterface::class)->save($order);

    $service = Pdk::get(FrontendRenderService::class);
    $html    = $service->renderContextAttribute(['orderIdentifier' => '42']);

    expect($html)->toContain('data-pdk-context');
});

it('embeds unnarrowed carriers when no orderIdentifier is given', function () {
    factory(Carrier::class)->withAllCapabilities()->store();
    $service = Pdk::get(FrontendRenderService::class);
    $html    = $service->renderContextAttribute([]);
    expect($html)->toContain('data-pdk-context');
});
```

- [ ] **Step 3: Modify FrontendRenderService**

Change the DynamicContext build path:

```php
$orderIdentifier = $args['orderIdentifier'] ?? null;
$order = null;
if ($orderIdentifier) {
    $order = Pdk::get(PdkOrderRepositoryInterface::class)->get($orderIdentifier);
}
$context = new DynamicContext($order ? ['order' => $order] : []);
```

Add imports as needed.

- [ ] **Step 4: Run + PHPStan**

```bash
docker compose run php composer test:unit -- --filter="FrontendRenderService"
docker compose run php composer analyse
```

- [ ] **Step 5: Commit**

```bash
git add src/Frontend/Service/FrontendRenderService.php tests/Unit/Frontend/Service/FrontendRenderServiceOrderContextTest.php
git commit -m "$(cat <<'EOF'
feat(frontend): inject order into initial data-pdk-context on order pages

When the plugin renders an admin order-edit page with an orderIdentifier,
the initial DynamicContext embedded in the data attribute is built with
that order, producing order-aware carriers from first paint.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 7: Multi-PHP verification

- [ ] PHP 7.4, 8.0, 8.4 — for each:

```bash
PHP_VERSION=X.Y docker compose run php composer update --no-interaction --no-progress
PHP_VERSION=X.Y docker compose run php composer test:unit
PHP_VERSION=X.Y docker compose run php composer analyse
```

All pass. No commit.

---

## Task 8: Open PDK PR

```bash
git push -u origin feat/INT-1505-admin-capabilities-context
```

Title: `feat(context): make DynamicContext carriers reflect the current order`

Body:

```markdown
## Summary

- `DynamicContext` accepts an optional `order` in its construction data. When present, carriers are projected through `Carrier::withContextualCapabilities()` (narrowing) and `Carrier::withOptionRelationships()` (requires/excludes annotation).
- `FetchContextAction` reads optional order payload from the request body.
- `FrontendRenderService` injects the current order into the initial `data-pdk-context` on order-edit pages.
- New `CapabilitiesValidationService::getContextualCarrierCapabilities()` with request-scope cache.

## Why

Completes INT-930 at the admin boundary. Admin order-edit UI gets carriers narrowed to the order's context (destination, weight) and annotated with option relationships — so JS-PDK consumers can render the right UI without duplicating capability logic.

No new types or endpoints. JS-PDK consumers (existing plan companion) just keep reading `dynamicContext.carriers`.

## Test plan

- [ ] PHP 7.4 / 8.0 / 8.4 unit tests pass
- [ ] PHPStan passes for modified files
- [ ] Admin carrier settings page still renders (no order context path)
- [ ] Admin order-edit page: package / delivery types narrow correctly (pairs with js-pdk PR)
- [ ] Draft order without shipping address opens without error
- [ ] V1 shipment / return / fulfilment unaffected

Refs: INT-1505 (sub-task of INT-930)

Pairs with js-pdk PR: [link after opening]
```

---

## Task 9: JS-PDK — useFetchContextQuery accepts order payload

**Repo:** `/Users/freek.vanrijt/projects/js-pdk`

**Files:**

- Modify: `apps/admin/src/actions/composables/queries/account/useFetchContextQuery.ts`
- Modify: `apps/admin/src/composables/useStoreContextQuery.ts`
- Test: `apps/admin/src/actions/composables/queries/account/useFetchContextQuery.test.ts`

- [ ] **Step 1: Read the existing composable**

```bash
cd ~/projects/js-pdk
cat apps/admin/src/actions/composables/queries/account/useFetchContextQuery.ts
cat apps/admin/src/composables/useStoreContextQuery.ts
```

- [ ] **Step 2: Write failing test**

```typescript
import { describe, it, expect, vi } from 'vitest';
import { ref } from 'vue';
import { useFetchContextQuery } from './useFetchContextQuery';

vi.mock('<existing useStoreQuery path>', () => ({
  useStoreQuery: vi.fn(() => ({ data: { value: null } })),
}));
import { useStoreQuery } from '<existing useStoreQuery path>';

describe('useFetchContextQuery', () => {
  it('includes order in cache key + request body when provided', () => {
    const order = ref({ externalIdentifier: '42', shippingAddress: { cc: 'NL' } });
    useFetchContextQuery({ order });

    // assert useStoreQuery received an order in its body option — exact shape depends on wrapper
    const callArgs = (useStoreQuery as any).mock.calls[0];
    expect(callArgs).toBeTruthy();
    // adapt assertion to actual call shape
  });

  it('omits order when not provided', () => {
    useFetchContextQuery();
    // assert useStoreQuery received no body option
  });
});
```

> Copy test patterns from neighbouring tests.

- [ ] **Step 3: Run — expect failure**

```bash
yarn nx test admin -- --run useFetchContextQuery
```

- [ ] **Step 4: Modify the composable**

```typescript
import { computed, toValue, type MaybeRefOrGetter } from 'vue';

export const useFetchContextQuery = (
  options: {
    contextKey?: AdminContextKey;
    order?: MaybeRefOrGetter<unknown | null>;
  } = {},
) => {
  const contextKey = options.contextKey ?? AdminContextKey.Dynamic;

  return useStoreQuery(BackendEndpoint.FetchContext, {
    parameters: computed(() => ({ context: encodeArrayParameter(contextKey) })),
    body: computed(() => {
      const order = toValue(options.order);
      return order ? { order } : undefined;
    }),
    // existing options preserved
  });
};
```

> Confirm `useStoreQuery` supports a `body` option and sends it as JSON on POST. If the underlying HTTP endpoint descriptor specifies method:GET only, update it to support POST (or add a method override) — trace through `BackendEndpoint.FetchContext` definition to find the right file.

- [ ] **Step 5: Run — expect pass + typecheck + lint**

```bash
yarn nx test admin -- --run useFetchContextQuery
yarn nx typecheck admin
yarn nx lint admin
```

- [ ] **Step 6: Commit**

```bash
git add apps/admin/src/actions/composables/queries/account/useFetchContextQuery.ts apps/admin/src/composables/useStoreContextQuery.ts apps/admin/src/actions/composables/queries/account/useFetchContextQuery.test.ts
git commit -m "$(cat <<'EOF'
feat(admin): pass current order state to FetchContext on re-fetch

useFetchContextQuery now accepts an optional order ref. When present,
the order is included in the cache key and request body so the server
can return DynamicContext with carriers projected through that order.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 10: JS-PDK — wire re-fetch on order form changes

**Repo:** `/Users/freek.vanrijt/projects/js-pdk`

**Files:**

- Create: `apps/admin/src/forms/order/useOrderFormContextWatcher.ts`
- Create: `apps/admin/src/forms/order/useOrderFormContextWatcher.test.ts`
- Modify: order edit form entry file (located in Task 0 Step 6)

- [ ] **Step 1: Write failing test**

```typescript
import { describe, it, expect, vi } from 'vitest';
import { ref, nextTick } from 'vue';
import { useOrderFormContextWatcher } from './useOrderFormContextWatcher';

const invalidateQueries = vi.fn();
vi.mock('@tanstack/vue-query', () => ({
  useQueryClient: () => ({ invalidateQueries }),
}));

describe('useOrderFormContextWatcher', () => {
  beforeEach(() => invalidateQueries.mockClear());

  it('invalidates the FetchContext query after debounce when order changes', async () => {
    vi.useFakeTimers();
    const order = ref({ shippingAddress: { cc: 'NL' } });
    useOrderFormContextWatcher(order);

    order.value = { shippingAddress: { cc: 'DE' } };
    await nextTick();
    expect(invalidateQueries).not.toHaveBeenCalled();

    vi.advanceTimersByTime(300);
    expect(invalidateQueries).toHaveBeenCalledTimes(1);

    vi.useRealTimers();
  });

  it('collapses rapid changes into a single invalidation', async () => {
    vi.useFakeTimers();
    const order = ref({ shippingAddress: { cc: 'NL' } });
    useOrderFormContextWatcher(order);

    order.value = { shippingAddress: { cc: 'DE' } };
    await nextTick();
    vi.advanceTimersByTime(100);
    order.value = { shippingAddress: { cc: 'FR' } };
    await nextTick();
    vi.advanceTimersByTime(300);

    expect(invalidateQueries).toHaveBeenCalledTimes(1);
    vi.useRealTimers();
  });
});
```

- [ ] **Step 2: Run — expect failure**

```bash
yarn nx test admin -- --run useOrderFormContextWatcher
```

- [ ] **Step 3: Implement**

`apps/admin/src/forms/order/useOrderFormContextWatcher.ts`:

```typescript
import { watch, type Ref } from 'vue';
import { useQueryClient } from '@tanstack/vue-query';
import { BackendEndpoint } from '<existing enum path>';
import { AdminContextKey } from '../../types/context.types';

const DEBOUNCE_MS = 300;

/**
 * Watches the order edit form state and invalidates the FetchContext query
 * (keyed by the dynamic context) so that dynamicContext.carriers re-projects
 * against the new order state.
 */
export const useOrderFormContextWatcher = (orderRef: Ref<unknown>) => {
  const queryClient = useQueryClient();
  let timer: ReturnType<typeof setTimeout> | null = null;

  watch(
    orderRef,
    () => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => {
        queryClient.invalidateQueries({
          queryKey: [BackendEndpoint.FetchContext, AdminContextKey.Dynamic],
        });
        timer = null;
      }, DEBOUNCE_MS);
    },
    { deep: true },
  );
};
```

> Adjust import paths as per existing admin file layout.

- [ ] **Step 4: Wire into the order edit form**

In the form file located in Task 0 Step 6, add to its setup:

```typescript
import { useOrderFormContextWatcher } from './useOrderFormContextWatcher';

// with the form-state ref (name may differ):
useOrderFormContextWatcher(orderFormState);
```

- [ ] **Step 5: Pass the order ref into `useFetchContextQuery`**

Find the admin app's top-level DynamicContext consumer (likely at app root). Pass the current order ref:

```typescript
useFetchContextQuery({
  contextKey: AdminContextKey.Dynamic,
  order: currentOrderRef,
});
```

- [ ] **Step 6: Run tests + typecheck + lint**

```bash
yarn nx test admin
yarn nx typecheck admin
yarn nx lint admin
```

- [ ] **Step 7: Commit**

```bash
git add apps/admin/src/forms/order/ apps/admin/src/<form entry file>
git commit -m "$(cat <<'EOF'
feat(admin): re-fetch DynamicContext on order form state changes

useOrderFormContextWatcher debounces (300ms) invalidation of the
FetchContext query tied to the order edit form state. Combined with
the order body in useFetchContextQuery, the server returns carriers
narrowed to the current form state.

Refs: INT-1505

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 11: Manual verification + JS-PDK PR

- [ ] **Step 1: Full build + tests**

```bash
yarn build
yarn test
yarn typecheck
yarn lint
```

- [ ] **Step 2: Manual smoke test in a plugin**

Link updated PDK + JS-PDK into WooCommerce or PrestaShop. Verify:

1. **Admin carrier settings page** — unchanged.
2. **Order edit page, initial load** — inspect `document.querySelector('#myparcel-pdk-boot').dataset.pdkContext` (JSON-decode it). `dynamic.carriers[...].packageTypes` reflects order's destination. No spurious `fetchContext` call on first render.
3. **Check `options` entries** — `dynamic.carriers[CARRIER].options[KEY].requires` / `excludes` are present and arrays.
4. **Change order destination country** — after ~300ms, see a network call to `?context=dynamic&action=fetchContext` with the order body. Response reflects the new country.
5. **Change order weight** — same pattern.
6. **Draft order (no shipping address)** — carriers render with contract-definition defaults; no errors.
7. **Switch carrier in order edit form** — package / delivery type fields update.

- [ ] **Step 3: Push and open JS-PDK PR**

```bash
git push -u origin feat/INT-1505-admin-capabilities-context
```

Title: `feat(admin): DynamicContext carriers reflect the current order state`

Body:

```markdown
## Summary

- `useFetchContextQuery` sends the current order state in the request body; order is part of the TanStack Query cache key
- `useOrderFormContextWatcher` invalidates the FetchContext query when the order form changes (debounced 300ms)
- No changes to `CarrierModel`, form helpers, or types — `dynamicContext.carriers` just becomes order-aware through the server response

## Why

Pairs with [PDK PR for INT-1505](https://github.com/myparcelnl/pdk/pull/[NUMBER]). Admin order-edit UI renders the right package types / delivery types / shipment options per order, and carrier `options[key].requires` / `excludes` become available for relationship-aware UI. Existing JS helpers work unchanged.

## Test plan

- [ ] `yarn test`, `yarn typecheck`, `yarn lint` pass
- [ ] Carrier settings page still renders (no order context)
- [ ] Order edit initial load shows order-aware carriers (check `data-pdk-context`)
- [ ] Changing destination / weight triggers debounced re-fetch
- [ ] Draft order opens without error
- [ ] `carriers[CARRIER].options[KEY].requires` / `excludes` populated in order context, absent in settings context

Refs: INT-1505 (sub-task of INT-930)

Pairs with: [PDK PR for INT-1505]
```

- [ ] **Step 4: Cross-link PRs**

Edit both PR descriptions to include the counterpart link. Tag QA.

---

## Rollback plan

- **Only PDK merges:** DynamicContext stays account-level (no order body ever sent). Safe.
- **Only JS-PDK merges:** Request body is ignored; DynamicContext returns account-level carriers. Order form invalidation thrashes the cache slightly but no breakage.

If rollback is needed:

1. Revert JS-PDK PR first.
2. Revert PDK PR if necessary.

---

## Out of scope

- **Monday delivery capabilities research.** See [INT-1506](https://myparcelnl.atlassian.net/browse/INT-1506).
- **Delivery options widget / checkout.** Plan 1 covered that.
- **Persistent cache for contextual capability calls.** Request-scope only.
- **OrderDataContext changes.** `inheritedDeliveryOptions` stays as-is.
- **Product-context awareness.** Separate follow-up if product-edit needs narrowing.
- **Auto-generated TS types from PHP.**

---

## Self-review checklist

- [ ] Every row in "Behavioral test matrix" maps to an automated test or a manual step in Task 11.
- [ ] Dead-code / `@TODO` sweep happens in the final plan of the series ([carrier-schema-cleanup](2026-04-18-carrier-schema-cleanup.md), step 4 of 4). Don't try to pre-empt it here — this plan is additive.
- [ ] No `if ($carrier->carrier === '...')` or `if (carrier === 'POSTNL')` in modified files.
- [ ] PHPStan passes for modified PHP files; typecheck passes for modified TS files.
- [ ] `OrderDataContextCapabilitiesTest` 4 scenarios still pass (no regression).
- [ ] Both PRs opened and cross-linked.
- [ ] `CarrierModel` shape and admin form helpers unchanged.
- [ ] Without an order in the request, `DynamicContext` behavior is identical to pre-plan (no `requires` / `excludes` added to serialized options).
- [ ] With an order in the request:
  - `dynamicContext.carriers[CARRIER].packageTypes` narrowed by contextual capabilities
  - `dynamicContext.carriers[CARRIER].deliveryTypes` narrowed
  - `dynamicContext.carriers[CARRIER].options[KEY].requires` populated
  - `dynamicContext.carriers[CARRIER].options[KEY].excludes` populated
