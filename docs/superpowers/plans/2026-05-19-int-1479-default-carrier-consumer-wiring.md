# Plan — PR 2 / INT-1479: Default carrier from shipping rules (consumer wiring)

## Context

Builds on PR 1 (INT-1586)'s `ImplicationsService`. This PR wires the service into the account lifecycle, persists the resolved default on `Shop`, migrates the 5 callers of the soon-to-be-removed `PropositionService::getDefaultCarrier()`, and deletes the hard-coded default block from proposition config.

Spec: `docs/superpowers/specs/2026-05-19-default-carrier-from-shipping-rules-design.md` (PR 2 section).
Parent plan / PR 1 context: `~/.claude/plans/partitioned-greeting-wombat.md`.

Why now: PR 1 landed the additive service with zero functional impact. PR 2 makes the user-facing behaviour change — default carrier reflects the shop's shipping rule (configured in the MyParcel backoffice) instead of a static plugin config.

Outcome: when the user saves a valid API key (or triggers any flow that runs `UpdateAccountAction`, which includes account refresh / debug switch / shop webhook), the shop's default carrier is fetched and persisted on `Shop`. All 5 historical callers of `PropositionService::getDefaultCarrier()` now read from the Shop. Proposition JSONs and the deprecated method are removed.

## Key audit findings that shape this plan

1. **No separate "debug refresh" action exists.** The spec spoke of "the refresh action within account debug" as if it were a distinct entry point. It isn't. Account refresh (including the debug switch-environment actions and shop webhooks) all route through `PdkBackendActions::UPDATE_ACCOUNT` which dispatches `UpdateAccountAction::handle`. Wiring `setShopDefaultCarrier()` into `updateAndSaveAccount` automatically covers refresh; no separate task.
2. **`HasCarrierAttribute::getCarrierAttribute()` returns non-null `Carrier`** (lines 33-42), and `Shipment\DeliveryOptions::getCarrierAttribute()` (lines 325-335) and `Fulfilment\Shipment` inline the same pattern. To preserve the existing public contract of these readers, the **default-resolution failure throws** (mirroring today's `PropositionService::getDefaultCarrier()` `RuntimeException`). The Shop attribute getter `defaultCarrierModel` stays nullable; consumers like `getCarrierAttribute` translate null → throw.
3. **`PostReturnShipmentsRequest::ensureReturnCapabilities`** (line 141) uses the default carrier as a _fallback_ when the user's selected carrier doesn't support returns. The fallback builds a notification text via `$defaultCarrier->carrier`. With a nullable default, this needs an explicit null-check before notifying — if no default is known, keep the original carrier and let the export attempt surface a more accurate error rather than silently switching to null. The "hard failure" framing in the spec resolves naturally to "skip the fallback when there's no default".
4. **Proposition `default` block is consumed only by `getDefaultCarrier()`.** Both `default.carrier.{id,name}` and the sibling `default.id` (contract id) become dead config once the method is deleted. The entire `default` block in `contracts.outbound` and `contracts.inbound` is safe to remove from `proposition-1.json`, `proposition-3.json`, `proposition-6.json`.
5. **`PropositionContracts` `inbound`/`outbound` properties are dead after deletion.** Confirmed via `rg`: the ONLY consumers of `$config->contracts->inbound` and `$config->contracts->outbound` are the two reads inside `PropositionService::getDefaultCarrier()`. Once that method is gone, both properties are unreachable. The sibling `availableForCustomCredentials` and the JSON's `contracts.enablePostNLCustomContract` are unrelated and stay. Task 4 cleanly cuts the feature: drop the `inbound` and `outbound` properties from `PropositionContracts.php` entirely (both `$attributes` and `$casts` entries plus the `@property` docblock lines) AND drop the corresponding `contracts.inbound` / `contracts.outbound` JSON keys from all three proposition-N.json files.

## Pre-work

- **Branch off PR 1's branch**, not `v4-capabilities`. PR 2 needs the `ImplicationsService` class to exist locally; that class lives only on `feat/INT-1586-shipping-rules-service` until PR 1 merges. Create `feat/INT-1479-default-carrier-from-shipping-rules` off `feat/INT-1586-shipping-rules-service`.
- **Rebase plan**: when PR 1 merges, rebase PR 2 onto `v4-capabilities`. Target the merge commit, not the squash, since GitHub squash-merge produces a single commit. The PR 2 branch then re-targets `v4-capabilities`.
- The same local composer / docker-compose overrides from PR 1 stay in effect (composer.json with `*@beta` constraint + path repo; `docker-compose.override.yml` mounting `../sdk`). These remain skip-worktree / gitignored.

## Tasks

### Task 1 — Shop attribute + virtual getter + ShopTest

**Files**:

- `src/Account/Model/Shop.php` (modify)
- `tests/Unit/Account/Model/ShopTest.php` (extend if exists, create otherwise)

Add a new persisted attribute `defaultCarrier` (V2 carrier name string, nullable) and a virtual property `defaultCarrierModel` resolving to `?Carrier` via `CarrierRepositoryInterface::find`.

Production change shape:

```php
/**
 * @property null|string         $defaultCarrier      // V2 carrier name (e.g. "POSTNL")
 * @property-read null|Carrier   $defaultCarrierModel // resolved via CarrierRepository
 */
class Shop extends Model
{
    public $attributes = [
        // …existing keys…
        'defaultCarrier' => null,
    ];

    protected $casts = [
        // …existing keys…
        'defaultCarrier' => 'string',
    ];

    public function getDefaultCarrierModelAttribute(): ?Carrier
    {
        if (! $this->defaultCarrier) {
            return null;
        }

        return Pdk::get(CarrierRepositoryInterface::class)->find($this->defaultCarrier);
    }
}
```

Confirm by reading `Shop.php` first — the property table is alphabetised in some PDK models. Match local ordering convention.

Tests (concrete assertions, no snapshots):

1. `defaultCarrier` defaults to `null` on a fresh Shop.
2. `defaultCarrierModel` returns `null` when `defaultCarrier` is unset.
3. `defaultCarrierModel` resolves to the Carrier returned by `CarrierRepositoryInterface::find` when set.
4. `defaultCarrierModel` returns `null` when the repository returns `null` for an unknown V2 name.
5. `defaultCarrier` V2 string round-trips through `toArray()` and `fill()`.

Mock `CarrierRepositoryInterface` via the PDK mocking utilities (`MockPdkInstance` + `UsesMockPdkInstance` — same pattern as PR 1's `ImplicationsServiceTest`).

**Guardrails**:

- Do NOT change other Shop attributes.
- Do NOT couple Shop directly to `ImplicationsService`. Shop only stores the resolved name and looks up via the repository.
- Do NOT use the legacy carrier id flow (`findByLegacyId`) here — use `find($v2Name)`.

### Task 2 — `UpdateAccountAction::setShopDefaultCarrier` wire-up + tests

**Files**:

- `src/App/Action/Backend/Account/UpdateAccountAction.php` (modify)
- `tests/Unit/App/Action/Backend/Account/UpdateAccountActionTest.php` (extend)

Inject `ImplicationsService` via the constructor (PHP-DI auto-wires). Call a new `protected setShopDefaultCarrier(Account $account): void` from `updateAndSaveAccount` immediately after `setShopCarriers($account)` (line ~172).

Production shape:

```php
protected function setShopDefaultCarrier(Account $account): void
{
    $shop = $account->shops->first();

    if (! $shop || ! $shop->id) {
        Logger::warning('Cannot fetch default carrier: no shop or shop id available');
        return;
    }

    $defaultCarrier = $this->implicationsService->getDefaultCarrierName($shop->id);

    // Preserve previously-stored value on transient error (decision β; see spec § Error handling).
    if ($defaultCarrier !== null) {
        $shop->defaultCarrier = $defaultCarrier;
    }
}
```

Tests (extend the existing `UpdateAccountActionTest`):

1. Writes V2 name to Shop on Service success.
2. Preserves prior `defaultCarrier` value when Service returns `null` (decision β).
3. Skips with warning log when shops collection is empty.
4. Does NOT call the Service when the API key is empty (short-circuit guards still hold).

Mock `ImplicationsService` via Mockery. Pre-seed Shop's `defaultCarrier` for test #2.

**Guardrails**:

- Do NOT call `setShopDefaultCarrier()` before `setShopCarriers()`. Order matters because Shop must already exist on the Account when we write to it.
- Do NOT add a TTL or in-Service cache — the Shop attribute IS the persistence layer.
- The `Logger::warning` is appropriate here (warning vs error) because the skip case is recoverable: the user can retry by saving the key.

### Task 3 — Migrate 5 call sites of `PropositionService::getDefaultCarrier()`

**Files**:

- `src/Carrier/Concern/HasCarrierAttribute.php` (modify)
- `src/Context/Model/GlobalContext.php` (modify)
- `src/Shipment/Request/PostReturnShipmentsRequest.php` (modify)
- `src/Fulfilment/Model/Shipment.php` (modify)
- `src/Shipment/Model/DeliveryOptions.php` (modify)
- Tests for each site (extend existing or create where needed)

Strategy: each site reads `$shop = Pdk::get(AccountSettingsServiceInterface::class)->getShop()` and uses `$shop?->defaultCarrierModel`. The five sites have different signatures, so they handle the null case differently:

| #   | Site                                                                      | Signature shape                                                                                                             | Null handling                                                                                                                                                                          |
| --- | ------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | `HasCarrierAttribute::getCarrierAttribute(): Carrier` (line 33-42)        | Non-null Carrier.                                                                                                           | Throw a `RuntimeException` with a clear message mirroring today's PropositionService throw. The trait's signature stays non-null.                                                      |
| 2   | `GlobalContext.php:81`                                                    | Used in payload construction; today already inside a defensive block.                                                       | Read `$shop?->defaultCarrierModel`. If null, omit the field (or set the existing fallback handled at the call site). Verify the surrounding code accepts absence.                      |
| 3   | `PostReturnShipmentsRequest::ensureReturnCapabilities` (line 141-162)     | Used as a fallback when the user's carrier doesn't support returns. Builds a notification using `$defaultCarrier->carrier`. | Null-check before fallback: if `$shop?->defaultCarrierModel` is null, **do not switch carriers**, keep the user's original. The notification text is suppressed (no fallback message). |
| 4   | `Fulfilment\Shipment.php:64`                                              | `$this->attributes['carrier'] ??= Utils::convertToName($default->id, Carrier::CARRIER_NAME_ID_MAP)`.                        | Drop the `convertToName` round-trip. New: `$this->attributes['carrier'] ??= $shop?->defaultCarrier` (uses persisted V2 string directly).                                               |
| 5   | `Shipment\DeliveryOptions::getCarrierAttribute(): Carrier` (line 325-335) | Non-null Carrier (same shape as #1).                                                                                        | Same throw pattern as #1.                                                                                                                                                              |

Common refactor before the per-site work: introduce or reuse a small inline pattern like `$shop?->defaultCarrierModel ?? throw new RuntimeException('No default carrier available …')` — but PHP 7.4 has no throw expression. Implement as an explicit two-line if/throw at each non-null site.

Tests:

- HasCarrierAttribute: resolves via the Shop default when no carrier is stored; throws when no Shop / no Shop default; returns the stored carrier when one is set.
- GlobalContext: payload includes V2 default when Shop has one; payload tolerates absence.
- PostReturnShipmentsRequest: when carrier supports returns → no fallback; when carrier doesn't support returns and Shop has a default → fallback + notification; when carrier doesn't support returns and Shop has NO default → keep original carrier, no notification.
- Fulfilment\Shipment: `carrier` attribute defaults to Shop's V2 string when not pre-set; explicit carrier wins.
- DeliveryOptions: resolves via the Shop default; throws when no Shop default and no carrier set.

**Guardrails**:

- Do NOT widen the `getCarrierAttribute()` return type from `Carrier` to `?Carrier` (that's a wider API change affecting all readers of `->carrier` on these models).
- Do NOT delete `PropositionService::getDefaultCarrier()` in this task — Task 4 owns that.
- Do NOT update `composer.json`.

### Task 4 — Delete `PropositionService::getDefaultCarrier()` + proposition config cleanup

**Files**:

- `src/Proposition/Service/PropositionService.php` (modify)
- `src/Proposition/Model/PropositionContracts.php` (modify)
- `config/proposition/proposition-1.json` (modify)
- `config/proposition/proposition-3.json` (modify)
- `config/proposition/proposition-6.json` (modify)
- Tests asserting against the removed method (delete or migrate)

Production changes:

1. Delete the `getDefaultCarrier()` method (lines ~178-201 of `PropositionService.php`).
2. Drop the unused `Carrier` import from the top of `PropositionService.php` if no other code in the file references it.
3. **Cleanly cut the feature from `PropositionContracts`**:
   - Remove `'inbound' => ['default' => []]` and `'outbound' => ['default' => []]` from `$attributes`.
   - Remove `'inbound' => 'array'` and `'outbound' => 'array'` from `$casts`.
   - Remove the `@property array $inbound …` and `@property array $outbound …` lines from the class docblock.
   - `$availableForCustomCredentials` stays (unrelated feature).
4. **Cleanly cut the feature from the proposition JSONs**: remove the entire `contracts.inbound` and `contracts.outbound` keys from all three proposition-N.json files. (`contracts.enablePostNLCustomContract` stays — sibling feature.) After the cut, the `contracts` block contains only `enablePostNLCustomContract`.

Verification:

- `rg "getDefaultCarrier\b" src/ tests/` returns no production code references after deletion.
- `rg "contracts->inbound|contracts->outbound|contracts\['inbound'\]|contracts\['outbound'\]" src/ tests/` returns nothing.
- `rg "inbound|outbound" src/Proposition/` returns no references outside docblocks of unrelated code.
- `rg '"inbound"|"outbound"' config/proposition/` returns nothing.
- `docker compose run --rm php composer test:unit` — full suite green.
- `docker compose run --rm php composer analyse` — PHPStan clean.

**Guardrails**:

- Do NOT delete tests covering OTHER `PropositionService` methods.
- Do NOT change the proposition JSON schema beyond removing `contracts.inbound` and `contracts.outbound` (no reordering, no whitespace churn outside the removed keys).
- Do NOT touch `availableForCustomCredentials` or `enablePostNLCustomContract` — both are unrelated features that survive the cut.
- Do NOT touch `PropositionConfig` itself unless it references `inbound`/`outbound` directly (read it first to be sure; if it just type-hints `PropositionContracts`, no change needed there).

## Verification (end-to-end)

After all 4 tasks land:

1. `docker compose run --rm php composer test:unit` — full suite green; new tests pass.
2. `docker compose run --rm php composer analyse` — PHPStan clean.
3. `rg "PropositionService::class.*getDefaultCarrier|->getDefaultCarrier" src/ tests/` — empty.
4. `rg "contracts->.*default" src/ tests/` — empty.
5. Manual smoke (post-PR-1 merge, against acceptance API): save API key → `Pdk::get(AccountSettingsService::class)->getShop()->defaultCarrier` returns the expected V2 string. Repeat by triggering UPDATE_ACCOUNT via the debug switch action — value refreshes.

## Out of scope

- SDK `composer.json` pin bump — PR 1 owns that at merge time.
- INT-1441 (legacy carrier name/ID mapping centralisation) — Task 3 site #4 drops one `Carrier::CARRIER_NAME_ID_MAP` use but does NOT touch the broader mapping infrastructure.
- INT-1266 (`MyParcelApiService` → SDK migration) — orthogonal.
- TTL on the Shop attribute — explicitly not part of the design.

## Branch, commit & PR shape

- Branch: `feat/INT-1479-default-carrier-from-shipping-rules` off `feat/INT-1586-shipping-rules-service`.
- After PR 1 merges, rebase onto the merged commit on `v4-capabilities`.
- Squash merge: one final commit. Body = the spec's PR 2 description verbatim.
- Footers on the squash commit: `Resolves INT-1479` + `Resolves INT-1504`.
- **Convention update**: per memory rule `project_int1504_commit_footer`, every commit (including WIP) carries `Resolves INT-1504`. The spec's earlier "WIP commits omit the resolves lines" guidance was incorrect; this plan supersedes it. Squash strips duplicates at merge time.
- PR stays in draft until PR 1 merges, then flip to ready.

## Open items / risks

- **`HasCarrierAttribute` triple-pattern**. Three places (HasCarrierAttribute trait, DeliveryOptions inline, Fulfilment\Shipment via the trait) currently inline the same "resolve carrier with default fallback" pattern. Task 3 migrates each independently rather than extracting a shared helper, because:
  - The trait already exists for two consumers.
  - DeliveryOptions deliberately inlined to avoid coupling.
  - Extracting a shared helper is YAGNI churn outside this PR's scope.
    If the migration reveals divergence (e.g. one site needs a different exception class), the inline pattern is preferable to a forced abstraction.
- **`PostReturnShipmentsRequest` fallback removal**. The current code silently switches the carrier when the user's pick doesn't support returns. With no default available, the new behaviour is "keep the user's carrier". This may surface an export failure further downstream that today is masked by the silent switch. Acceptable per the spec's "actionable error" principle, but worth flagging in the PR description for testers.
- **PropositionContracts model cut**. Task 4 removes the `inbound` and `outbound` properties entirely (no empty placeholders). If implementer discovers a hidden consumer that wasn't caught by the audit grep (e.g. dynamic property access, magic getters, reflection in fixtures), they should escalate rather than reintroduce a placeholder.
- **Shop test file may not exist yet**. If `tests/Unit/Account/Model/ShopTest.php` is absent, Task 1's implementer creates it following the closest Pest v1 pattern (e.g., `tests/Unit/Carrier/Model/CarrierTest.php`).
