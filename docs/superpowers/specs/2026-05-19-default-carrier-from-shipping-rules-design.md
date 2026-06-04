# Default carrier from shipping-rules implications

- **Tickets**: [INT-1479](https://myparcelnl.atlassian.net/browse/INT-1479) (parent), [INT-1586](https://myparcelnl.atlassian.net/browse/INT-1586) (sub-task)
- **Author**: Freek van Rijt
- **Date**: 2026-05-19
- **Status**: Pending review
- **Parent epic**: INT-1504 (v4-capabilities cleanup)

## Summary

Replace the hard-coded "default carrier" entries in `proposition-*.json` with a value fetched from the CoreAPI shipping-rules `implications` endpoint, persisted on the `Shop` model, and resolved to a `Carrier` via the existing `CarrierRepository`.

The fetch runs when the API key is saved (in `UpdateAccountAction::updateAndSaveAccount`) and when the account-debug "refresh" action fires. There is no TTL, no fallback to proposition config, and no retries — the persisted Shop attribute is the single source of truth between fetches.

## Goals

- Remove the `contracts.{outbound,inbound}.default.carrier` blocks from proposition config and `PropositionService::getDefaultCarrier()`.
- Introduce a PDK-side service that calls the SDK's new `CoreApiPrivate\ShippingRuleApi::getShippingRuleImplications($shop_id)` and returns the implied default carrier as a V2 name.
- Persist the resolved V2 carrier name on `Shop`.
- Migrate the 5 existing callers of `PropositionService::getDefaultCarrier()` to read from the Shop, with explicit null-handling per site.

## Non-goals

- No SDK work. The `CoreApiPrivate` client and `ShippingRuleApi` already exist on a feature branch (pending merge) in `myparcelnl/sdk`. This spec assumes that branch lands and the SDK pin is bumped.
- No additional shipping-rules methods. INT-1586 explicitly forbids unused endpoint wrappers.
- No per-country / per-region implications. We only ever call the endpoint with `shop_id` — the default-rule path.
- No TTL-based refresh.
- No proposition-config fallback during the migration window.
- Inbound/outbound distinction is dropped (no callers use `$outbound = false` today).

## Constraints

- PHP 7.4 compatible.
- V4-capabilities branch already accepts breaking changes; we drop `PropositionService::getDefaultCarrier()` outright rather than deprecate.
- PDK consumers: PrestaShop + WooCommerce plugins only.

## Architecture

```
   UpdateAccountAction::updateAndSaveAccount
     setShopCarriers($account)
     setShopDefaultCarrier($account)   ← NEW: Service call + write to $shop->defaultCarrier
     pdkAccountRepository->store($account)

   Account debug "refresh" action
     same fetch + write path

         │
         ▼ uses
   src/SdkApi/Service/CoreApiPrivate/ShippingRule/ImplicationsService.php   NEW
     extends AbstractCoreApiPrivateService   ← NEW base (mirror of AbstractCoreApiService
                                                against CoreApiPrivate\Configuration)
     + getDefaultCarrierName(int $shopId): ?string
         pure passthrough: instantiate ShippingRuleApi,
         call getShippingRuleImplications($shop_id),
         return RefShippingRulesImplications->carrier_id (as V2 string) or null on error

         │
         ▼
   Sdk\Client\Generated\CoreApiPrivate\Api\ShippingRuleApi

   Shop  (extended)
     attribute  defaultCarrier      ← NEW persisted V2 string, e.g. "POSTNL"
     getter     getDefaultCarrierModelAttribute(): ?Carrier  ← NEW, resolves via CarrierRepository

   PropositionService::getDefaultCarrier()              DELETED
   proposition-*.json contracts.{out,in}bound.default   DELETED
   5 call sites                                          MIGRATED to $shop->defaultCarrier
```

Three boundaries:

- **PDK service** (`ImplicationsService`): owns the wire concern, directly uses the generated `ShippingRuleApi` and `RefShippingRulesImplications` — no intermediate wrapper layer. Pure passthrough; no cache of its own.
- **Shop model**: owns the persisted state (V2 string) and the model→Carrier resolution.
- **`UpdateAccountAction`**: owns the lifecycle (when to fetch and write).

The Shop attribute is the only persistence layer. There is no `StorageInterface`-backed cache; if a second persistence layer were added, the two would drift.

## Components

### New: `AbstractCoreApiPrivateService`

`src/SdkApi/Service/CoreApiPrivate/AbstractCoreApiPrivateService.php`

Mirror of `AbstractCoreApiService` typed against `CoreApiPrivate\Configuration`.

```php
abstract class AbstractCoreApiPrivateService extends AbstractSdkApiService
{
    public function getApiConfig(): CoreApiPrivateConfiguration
    {
        return $this->applyConfigSettings(new CoreApiPrivateConfiguration());
    }
}
```

`AbstractSdkApiService::applyConfigSettings()` accepts `object` and returns `object`, so the private `Configuration` flows through unchanged — no widening needed.

### New: `ImplicationsService`

`src/SdkApi/Service/CoreApiPrivate/ShippingRule/ImplicationsService.php`

Single public method. No other endpoint methods are wrapped — INT-1586 explicitly forbids dead code.

```php
class ImplicationsService extends AbstractCoreApiPrivateService
{
    private ShippingRuleApi $api;
    private CarrierRepositoryInterface $carrierRepository;

    public function __construct(CarrierRepositoryInterface $carrierRepository)
    {
        $this->api               = new ShippingRuleApi($this->createGuzzleClient(), $this->getApiConfig());
        $this->carrierRepository = $carrierRepository;
    }

    public function getDefaultCarrierName(int $shopId): ?string
    {
        try {
            $response     = $this->api->getShippingRuleImplications($shopId);
            $implications = $response->getData()->getImplications();

            if (empty($implications)) {
                return null;
            }

            $carrierId = $implications[0]->getCarrierId();

            if ($carrierId === null) {
                return null;
            }

            $carrier = $this->carrierRepository->findByLegacyId((int) $carrierId);

            return $carrier ? $carrier->carrier : null;
        } catch (ApiException $e) {
            // LoggingMiddleware already records the HTTP failure at the Guzzle transport layer.
            return null;
        } catch (Throwable $e) {
            // Unexpected programmer error — log so it doesn't get silently masked.
            Logger::error('Unexpected error fetching default carrier name', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);

            return null;
        }
    }
}
```

The catch is split deliberately: `ApiException` is the expected SDK failure path and is already logged by `LoggingMiddleware::forApiRequests()` (pushed onto every SDK service's Guzzle handler stack), so we silently return `null`. Any other `Throwable` indicates something unexpected (response-shape change, programmer error in the chain) — log before returning `null` so real bugs surface in logs instead of disappearing.

### Extended: `Shop`

`src/Account/Model/Shop.php`

```php
/**
 * @property null|string         $defaultCarrier        // V2 carrier name, e.g. "POSTNL"
 * @property-read null|Carrier   $defaultCarrierModel   // resolved Carrier (virtual)
 */
class Shop extends Model
{
    public $attributes = [
        // …existing…
        'defaultCarrier' => null,
    ];

    protected $casts = [
        // …existing…
        'defaultCarrier' => 'string',
    ];

    public function getDefaultCarrierModelAttribute(): ?Carrier
    {
        if (! $this->defaultCarrier) {
            return null;
        }

        return Pdk::get(CarrierRepository::class)->find($this->defaultCarrier);
    }
}
```

The persisted attribute holds the V2 string (round-trips cleanly through storage). The model getter is a virtual property — distinct name avoids a collision with the underlying attribute.

### Extended: `UpdateAccountAction`

`src/App/Action/Backend/Account/UpdateAccountAction.php`

Add `ImplicationsService` to the constructor; add `setShopDefaultCarrier()`; call it from `updateAndSaveAccount` right after `setShopCarriers()`.

```php
protected function setShopDefaultCarrier(Account $account): void
{
    $shop = $account->shops->first();

    if (! $shop || ! $shop->id) {
        Logger::warning('Cannot fetch default carrier: no shop or shop id available');
        return;
    }

    $defaultCarrier = $this->implicationsService->getDefaultCarrierName($shop->id);

    // Preserve previously-stored value on transient error (decision β; see "Error handling").
    if ($defaultCarrier !== null) {
        $shop->defaultCarrier = $defaultCarrier;
    }
}
```

### Refresh debug action

The account-debug "refresh" path must re-run the same fetch + write. The exact action class is identified during plan writing — at minimum we wire `setShopDefaultCarrier()` into whatever entry point the debug button invokes.

### Deleted

- `PropositionService::getDefaultCarrier()` and its `@TODO` block.
- `contracts.outbound.default.carrier` and `contracts.inbound.default.carrier` from `config/proposition/proposition-1.json`, `-3.json`, `-6.json`. If `default` becomes empty after removal, drop the `default` key too. If `default` carries siblings still in use (e.g. `contractId`), only remove the `carrier` child.
- Any tests asserting against `PropositionService::getDefaultCarrier()` (replaced by the new Shop/Service tests).

## Data flow

### Happy path: user saves a valid API key

```
Admin UI ── POST /account/update {api_key: …} ──▶ UpdateAccountAction::handle
                                                       │
                                                       ▼
                                               updateAccountSettings()       (persists key)
                                                       │
                                                       ▼
                                               updateAndSaveAccount()
                                                       │
                                                       ├─ accountRepository->getAccount()
                                                       ├─ propositionService->setActivePropositionId(...)
                                                       ├─ setShopCarriers($account)
                                                       ├─ setShopDefaultCarrier($account)
                                                       │     │
                                                       │     ▼
                                                       │   ImplicationsService::getDefaultCarrierName($shop->id)
                                                       │     │
                                                       │     ▼
                                                       │   ShippingRuleApi::getShippingRuleImplications($shop_id)
                                                       │     │
                                                       │     ▼
                                                       │   $shop->defaultCarrier = "POSTNL"
                                                       │
                                                       ├─ pdkAccountRepository->store($account)
                                                       └─ setApiKeyValidity(true)
```

### Manual refresh

Same code path as the happy path. `setShopDefaultCarrier()` runs and overwrites with the new value when the Service returns one.

### Missing / fetch failed

- **No API key**: short-circuits at `if (! $accountSettings->apiKey)`. Account is wiped. No Shop ⇒ no `defaultCarrier`.
- **API key invalid (401)**: `accountRepository->getAccount()` throws; the surrounding `try` wipes the account and re-throws. `setShopDefaultCarrier()` never runs.
- **Implications endpoint errors**: `ImplicationsService::getDefaultCarrierName()` catches `ApiException` (already logged by `LoggingMiddleware`) and other `Throwable`s (logged explicitly at error level), then returns `null`. `setShopDefaultCarrier()` does **not** overwrite the previously stored value (decision β).
- **Unsupported `carrier_id`**: persisted on Shop; `Shop::getDefaultCarrierModelAttribute()` returns `null` via `CarrierRepository::find()`; `Carrier::isSupported()` logs the warning.

### Read by a caller

```
some site
   │
   ▼
$shop    = accountSettingsService->getShop()
$carrier = $shop?->defaultCarrierModel        // ?Carrier
```

No API call on read. Local state only.

## Error handling

| #   | Failure                                                         | Where                                         | Behaviour                                                                                       |
| --- | --------------------------------------------------------------- | --------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| 1   | No API key set                                                  | `UpdateAccountAction` guard                   | `setShopDefaultCarrier()` never runs.                                                           |
| 2   | API key invalid (401)                                           | `accountRepository->getAccount()` throws      | Existing try/catch wipes account.                                                               |
| 3   | Implications endpoint 5xx / network blip                        | `ImplicationsService` `ApiException` catch    | Silent return `null` (LoggingMiddleware already logged). **Preserve previous Shop value.**      |
| 4   | Response deserializes; `carrier_id` empty / unsupported V2 name | Shop persists string; `find()` returns `null` | Attribute getter returns `null`. Existing `Carrier::isSupported()` warning fires when consumed. |
| 5   | Shop has no `id` yet                                            | Defensive guard in `setShopDefaultCarrier`    | Skip fetch, log warning.                                                                        |

### Preserve-on-error (β)

`setShopDefaultCarrier()` writes only when the Service returns non-null. A single flaky API call cannot null the default for every caller until the next successful refresh. The manual refresh button is the recovery path for legitimately-changed server-side state.

### Deliberate omissions

- No retries. Manual refresh / next key-save is the retry surface.
- No fallback to proposition config. INT-1479 removes that path; reintroducing it as an error fallback would recreate the dependency we're deleting.
- No request-scoped Service cache. The Service is only called from `UpdateAccountAction` and the debug refresh — once per invocation.

## Migration of existing call sites

| #   | File:line                                                 | Migration                                                                                                                                                                               |
| --- | --------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | `src/Carrier/Concern/HasCarrierAttribute.php:38`          | Read `$shop?->defaultCarrierModel`. If null, propagate `null` (callers already accept missing carrier in this fallback path).                                                           |
| 2   | `src/Context/Model/GlobalContext.php:81`                  | Read from Shop. Null path means "no default known yet"; the context payload tolerates absence post-INT-1479.                                                                            |
| 3   | `src/Shipment/Request/PostReturnShipmentsRequest.php:148` | Read from Shop. **Null must produce an actionable error** — return shipments cannot silently submit without a carrier.                                                                  |
| 4   | `src/Fulfilment/Model/Shipment.php:64`                    | Drops the `convertToName(..., Carrier::CARRIER_NAME_ID_MAP)` round-trip. New shape: `$this->attributes['carrier'] ??= $shop?->defaultCarrier;` (uses the persisted V2 string directly). |
| 5   | `src/Shipment/Model/DeliveryOptions.php:330`              | Read from Shop. Existing fallback machinery for missing carrier absorbs the null branch.                                                                                                |

Call site #3 is the only hard-failure surface and is pinned by a test (see Testing → call-site sanity).

## Testing

Pest v1, concrete assertions, no value-sensitive snapshots, mocks via PDK utilities, run via Docker (`yarn run test:unit`).

### 1. `ImplicationsService` unit tests

`tests/Unit/SdkApi/Service/CoreApiPrivate/ShippingRule/ImplicationsServiceTest.php`

- Returns V2 name when API succeeds.
- Returns `null` on `ApiException`.
- Passes `shop_id` unchanged; no extra `country`/`region`/`type` arguments.
- Class surface only exposes `getDefaultCarrierName` (guards INT-1586 scope creep).

### 2. `Shop::getDefaultCarrierModelAttribute()`

`tests/Unit/Account/Model/ShopTest.php` (existing or new)

- Returns `null` when `defaultCarrier` unset.
- Resolves to Carrier via `CarrierRepository::find()`.
- Returns `null` for unsupported V2 names.
- V2 string round-trips through `toArray()` / `fill()`.

### 3. `UpdateAccountAction::setShopDefaultCarrier`

Extend `tests/Unit/App/Action/Backend/Account/UpdateAccountActionTest.php`.

- Writes V2 name on Service success.
- Preserves prior value when Service returns `null` (decision β).
- Skips when no Shop / no shop id; warning logged.
- No fetch when API key empty.

### 4. Debug refresh

Identified during plan writing. Minimum: refresh re-runs fetch and overwrites; refresh populates a previously-null attribute on success.

### 5. Call-site sanity (one focused test per site)

- `HasCarrierAttribute`: returns `null` when shop has no default; resolved Carrier when set.
- `GlobalContext`: payload includes V2 default carrier when set; absent when null.
- `PostReturnShipmentsRequest`: explicit, actionable failure when Shop has no default. **This test pins our chosen failure mode.**
- `Fulfilment\Shipment`: `carrier` attribute defaults to Shop's V2 string when not pre-set; untouched when pre-set.
- `DeliveryOptions::carrier()`: returns Shop's `defaultCarrierModel` when none supplied; respects explicit carrier when supplied.

### Out of scope for testing

- The `ShippingRuleApi` wire format (SDK's contract; mocked at the API class boundary).
- Specific carrier semantics (per PDK testing rule: opaque V2 strings as test data).
- Proposition JSON diffs (checked in; no runtime test needed).

## Dependencies & sequencing

- Blocked on SDK PR landing the `CoreApiPrivate` namespace and `ShippingRuleApi` (currently on branch `feat/placeholder-private-coreapi-client-for-shipping-rules` in `myparcelnl/sdk`).
- Composer pin bumps to the SDK version that publishes that namespace.
- This work is part of the v4-capabilities cleanup (INT-1504). Commit footer must include `Resolves INT-1504`.
- Coordinates with INT-1441 (SDK mapping centralisation) — we deliberately do not touch the V1↔V2 mapping layer; V2 strings flow end-to-end. If INT-1441 lands first the design is unaffected.

## Branching, commits & PRs

The work splits into two PRs, one per Jira ticket. Both target the `v4-capabilities` branch. PR 1 is additive only and merges first; PR 2 wires up consumers and removes the old path.

### PR 1 — INT-1586: PDK service for shipping-rules implications

Scope matches the sub-task definition exactly: a single PDK service wrapping `ShippingRuleApi::getShippingRuleImplications`. No callers wired up yet — that's PR 2's job.

- **Branch**: `feat/INT-1586-shipping-rules-service`
- **Includes**:
  - `src/SdkApi/Service/CoreApiPrivate/AbstractCoreApiPrivateService.php`
  - `src/SdkApi/Service/CoreApiPrivate/ShippingRule/ImplicationsService.php`
  - `tests/Unit/SdkApi/Service/CoreApiPrivate/ShippingRule/ImplicationsServiceTest.php`
  - `composer.json` pin bump (once SDK PR is merged & released)
- **PR title** (end-user framing): `feat(account): add shipping-rules client for default carrier lookup`
- **PR description** (same shape as squash commit body):

  ```
  feat(account): add shipping-rules client for default carrier lookup

  Adds the PDK-side service that calls the CoreAPI shipping-rules
  `implications` endpoint to retrieve the default carrier configured
  for a shop. No callers are wired up in this PR; the service is
  additive infrastructure consumed by the follow-up that replaces
  the hard-coded default in proposition config.

  No functional change for end users in this PR — default carrier
  selection still flows from proposition config until INT-1479 lands.

  Resolves INT-1586
  Resolves INT-1504
  ```

### PR 2 — INT-1479: derive default carrier from shipping rules

Wires the Service into the account lifecycle, persists on Shop, migrates the 5 call sites, removes the proposition-config path.

- **Branch**: `feat/INT-1479-default-carrier-from-shipping-rules`
- **Includes**:
  - `Shop` model: new `defaultCarrier` attribute + `defaultCarrierModel` getter.
  - `UpdateAccountAction`: `setShopDefaultCarrier()`, constructor dependency on `ImplicationsService`.
  - Account-debug refresh action: re-runs `setShopDefaultCarrier()`.
  - 5 call-site migrations (`HasCarrierAttribute`, `GlobalContext`, `PostReturnShipmentsRequest`, `Fulfilment\Shipment`, `DeliveryOptions`).
  - `PropositionService::getDefaultCarrier()` deletion.
  - `config/proposition/proposition-*.json` cleanup.
  - Tests for Shop, `UpdateAccountAction`, debug refresh, and the 5 call-site sanity checks.
- **PR title** (end-user framing): `feat(account): default carrier follows your shop's shipping rules`
- **PR description**:

  ```
  feat(account): default carrier follows your shop's shipping rules

  The default carrier used for shipments, returns, and the checkout
  delivery options now reflects the shipping rule configured for your
  shop in the MyParcel backoffice. Changes you make there propagate
  to the plugin on the next API-key save or via the "refresh" action
  in account debug.

  Previously the default was a static value baked into the plugin's
  proposition configuration; updating it required a plugin release.

  No new settings. Existing carrier preferences set per shipment are
  unaffected — the default only applies when no carrier is selected.

  Resolves INT-1479
  Resolves INT-1504
  ```

### Commit shape within each PR

- One PR = one squash merge. Internal commits during development can be granular; the squashed commit body matches the PR description above.
- Conventional-commit prefix: `feat(account):` for both PRs (capability surface, not infra).
- Every commit footer includes `Resolves INT-1504`. PR-defining commits also include the relevant `Resolves INT-XXXX` for the ticket being closed.
- Internal/WIP commits during development omit the `Resolves` lines so squash doesn't accumulate them.

### Jira linkage

- PR 1's GitHub PR auto-links to INT-1586 via the branch name (`INT-1586` token) and the `Resolves INT-1586` footer.
- PR 2 auto-links to INT-1479 the same way.
- Both PRs also list INT-1504 via the footer so the epic captures the cleanup.
- If additional sub-tasks are created later (e.g. for the account-debug refresh wire-up if it grows beyond a one-liner), each gets its own branch + PR following the same pattern.
