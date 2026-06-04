# Cross-cutting const migrations — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Three independent capability-migration cleanups that don't fit any single pattern:

1. **XCut B-2 — `Account::FEATURE_ORDER_NOTES`:** delete the `@deprecated` hand-declared PDK constant; callers use the SDK's `IamApi\Model\Feature::ORDER_NOTES` (already wrapped on PDK side as `PdkAccountFeaturesService::FEATURE_ORDER_NOTES`).
2. **XCut B-3 — `WeightServiceInterface::DIGITAL_STAMP_RANGES`:** delete the unused `@deprecated` constant outright (0 callers).
3. **XCut B-4 — `CarrierSettingsItemView` delivery-type loop:** stop relying on `defined(CarrierSettings::ALLOW_*)` to look up per-type setting keys; construct the keys dynamically via `'allow' + ucfirst($deliveryType)` (and `'priceDeliveryType' + ucfirst(...)`). Add a centralised helper so the key construction lives in one place.

**Architecture:** Each item is an independent cleanup. They're bundled together because each is small and they share the "remove hardcoded const → derive from a canonical source" theme.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep. XCut B-2 and B-4 touch plugin code — coordinated plugin PRs may be required.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Cross-cutting B-2, B-3, B-4.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`. No hard dependencies.

---

## Scope summary

### B-2: `Account::FEATURE_ORDER_NOTES`

| Symbol                                                           | Current state                                                                                                                           | Migration                                                                                                                                                                           |
| ---------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Pdk\Account\Model\Account::FEATURE_ORDER_NOTES = 'ORDER_NOTES'` | `@deprecated Use \MyParcelNL\Sdk\Client\Generated\IamApi\Model\Feature::ORDER_NOTES instead`. Used by `PostOrderNotesAction` + 3 tests. | Migrate callers to `PdkAccountFeaturesService::FEATURE_ORDER_NOTES` (which already aliases the SDK enum) or directly to `Feature::ORDER_NOTES`. Then delete the const on `Account`. |

PDK has 4 caller files (PostOrderNotesAction + 3 tests). Plugin scan to be run.

### B-3: `WeightServiceInterface::DIGITAL_STAMP_RANGES`

| Symbol                                                           | Current state                                                                                  | Migration                                      |
| ---------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| `Pdk\Base\Contract\WeightServiceInterface::DIGITAL_STAMP_RANGES` | `@deprecated use Pdk::get('digitalStampRanges'). Will be removed in v3.0.0`. 0 callers in PDK. | Verify 0 plugin callers, then delete outright. |

If a plugin still references the const, migrate that plugin to `Pdk::get('digitalStampRanges')` in a coordinated PR first.

### B-4: `CarrierSettingsItemView` delivery-type loop

| Block                                                                       | Current state                                                                                                                                                                                                                                                                                                | Migration                                                                                                                                                                                                                                                                                         |
| --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `CarrierSettingsItemView::getDeliveryTypeSettings()` (around lines 540-570) | Iterates `$this->carrier->deliveryTypes` (capabilities-driven), but maps each type to a HARDCODED const lookup via `\defined(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType))`. Silently skips types whose corresponding const doesn't exist. The `@TODO` in the file calls out this misfit. | Construct keys dynamically: `'allow' . ucfirst($deliveryType)` and `'priceDeliveryType' . ucfirst($deliveryType)`. Drop the `defined()` checks. Centralise the construction in a small static helper on `CarrierSettings` (or a dedicated key-builder class) so the formula has exactly one home. |

This change makes the loop work for ANY delivery type the capabilities API emits, not just those with a pre-declared `ALLOW_*` const.

---

## File structure

| File                                                           | Action                    | Responsibility                                                                                      |
| -------------------------------------------------------------- | ------------------------- | --------------------------------------------------------------------------------------------------- |
| `src/Account/Model/Account.php`                                | Modify                    | Delete the `FEATURE_ORDER_NOTES` const after migration.                                             |
| `src/App/Action/Backend/Order/PostOrderNotesAction.php`        | Modify                    | Replace `Account::FEATURE_ORDER_NOTES` with `PdkAccountFeaturesService::FEATURE_ORDER_NOTES`.       |
| `tests/Unit/Account/Service/AccountSettingsServiceTest.php`    | Modify                    | 3 references to migrate.                                                                            |
| `tests/Bootstrap/MockWhoamiService.php`                        | Verify                    | Already uses `PdkAccountFeaturesService::FEATURE_ORDER_NOTES` per the grep; no change needed.       |
| `src/Base/Contract/WeightServiceInterface.php`                 | Modify                    | Delete the `DIGITAL_STAMP_RANGES` const.                                                            |
| `src/Frontend/View/CarrierSettingsItemView.php`                | Modify                    | Replace `defined()` checks with calls to the shared `SettingKey` utility.                           |
| `src/Base/Support/SettingKey.php`                              | Create                    | New pure-string utility holding the `allow`/`price`/`export`/`priceDeliveryType` key formulas.      |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php` | Modify                    | Delegate `getCarrierSettingsKey`/`getAllowSettingsKey`/`getPriceSettingsKey` to `SettingKey`.       |
| `tests/Unit/Base/Support/SettingKeyTest.php`                   | Create                    | Unit tests for the new utility.                                                                     |
| Plugin repos                                                   | Inspect; modify if needed | Plugin scan determines whether any of the three items has plugin callers requiring coordinated PRs. |

---

## Task 1: Pre-flight + inventory

**Files:** No edits.

- [ ] **Step 1: Verify branch**

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 2: Baseline tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log; echo "exit: $?"
```

- [ ] **Step 3: Inventory `Account::FEATURE_ORDER_NOTES` callers**

```bash
rg -n 'Account::FEATURE_ORDER_NOTES\b' src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Expected callers in PDK (per earlier survey):

- `src/Account/Model/Account.php` (declaration)
- `src/App/Action/Backend/Order/PostOrderNotesAction.php` (1)
- `tests/Unit/Account/Service/AccountSettingsServiceTest.php` (3)

Surface any plugin hits to the user.

- [ ] **Step 4: Inventory `DIGITAL_STAMP_RANGES` callers**

```bash
rg -n '\bDIGITAL_STAMP_RANGES\b' src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Expected: only the declaration line in `src/Base/Contract/WeightServiceInterface.php`. Any other hit means a caller exists that must migrate to `Pdk::get('digitalStampRanges')` first.

- [ ] **Step 5: Confirm the `CarrierSettingsItemView` loop shape**

```bash
sed -n '540,575p' src/Frontend/View/CarrierSettingsItemView.php
```

Confirm the two `\defined(...)` checks bound by `strtoupper($deliveryType)` are still present.

- [ ] **Step 6: No commit.**

---

## Task 2: B-2 — migrate `Account::FEATURE_ORDER_NOTES` callers

**Files:**

- Modify: `src/App/Action/Backend/Order/PostOrderNotesAction.php`
- Modify: `tests/Unit/Account/Service/AccountSettingsServiceTest.php`

- [ ] **Step 1: Update `PostOrderNotesAction`**

In `src/App/Action/Backend/Order/PostOrderNotesAction.php`, find:

```php
if (! AccountSettings::hasSubscriptionFeature(Account::FEATURE_ORDER_NOTES)) {
```

Replace with:

```php
if (! AccountSettings::hasSubscriptionFeature(PdkAccountFeaturesService::FEATURE_ORDER_NOTES)) {
```

Update the `use` imports: drop `use MyParcelNL\Pdk\Account\Model\Account;` if no longer needed in this file (it might still be referenced for other purposes — grep first). Add `use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;`.

- [ ] **Step 2: Update `AccountSettingsServiceTest` (3 references)**

In `tests/Unit/Account/Service/AccountSettingsServiceTest.php`, replace each `Account::FEATURE_ORDER_NOTES` with `PdkAccountFeaturesService::FEATURE_ORDER_NOTES`. Update the `use` imports accordingly.

- [ ] **Step 3: Verify no remaining `Account::FEATURE_ORDER_NOTES` in PDK src/ or tests/**

```bash
rg 'Account::FEATURE_ORDER_NOTES\b' src/ tests/ --type=php
```

Expected: only the declaration on `src/Account/Model/Account.php` (deleted in Step 5).

- [ ] **Step 4: Run tests**

```bash
docker compose run --rm php composer test -- --filter='AccountSettingsService|PostOrderNotesAction' 2>&1 | tail -30
```

Expected: pass.

- [ ] **Step 5: Delete the const on `Account`**

In `src/Account/Model/Account.php`, find:

```php
/**
 * @deprecated Use \MyParcelNL\Sdk\Client\Generated\IamApi\Model\Feature::ORDER_NOTES instead.
 */
public const FEATURE_ORDER_NOTES = 'ORDER_NOTES';
```

Delete the const declaration and its docblock.

- [ ] **Step 6: Verify**

```bash
rg '\bAccount::FEATURE_ORDER_NOTES\b' src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

Expected: no output (if plugin scan returns hits, those are still in scope for a coordinated plugin PR — surface to user).

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 7: No commit yet.**

---

## Task 3: B-3 — delete `WeightServiceInterface::DIGITAL_STAMP_RANGES`

**Files:** Modify `src/Base/Contract/WeightServiceInterface.php`.

- [ ] **Step 1: Confirm 0 callers**

```bash
rg '\bDIGITAL_STAMP_RANGES\b' src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

Expected: only the declaration in `WeightServiceInterface.php`. If a plugin reference appears, migrate the plugin to `Pdk::get('digitalStampRanges')` in a coordinated PR first.

- [ ] **Step 2: Delete the const**

In `src/Base/Contract/WeightServiceInterface.php`, find:

```php
/**
 * @deprecated use Pdk::get('digitalStampRanges'). Will be removed in v3.0.0
 */
public const DIGITAL_STAMP_RANGES = [
    // ...
];
```

Delete the const declaration and its docblock.

- [ ] **Step 3: Verify**

```bash
rg '\bDIGITAL_STAMP_RANGES\b' src/ tests/ --type=php
```

Expected: no output.

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 4: No commit yet.**

---

## Task 4: B-4 — extract a `SettingKey` utility + make the delivery-type loop dynamic

**Files:**

- Create: `src/Base/Support/SettingKey.php` — new pure-string utility.
- Modify: `src/App/Options/Definition/AbstractOrderOptionDefinition.php` — delegate to the utility.
- Modify: `src/Frontend/View/CarrierSettingsItemView.php` — replace `defined()` checks with utility calls.

**Why the utility:** the `'allow' . ucfirst(...)` and `'price' . ucfirst(...)` formulas already live on `AbstractOrderOptionDefinition::getAllowSettingsKey()` and `::getPriceSettingsKey()` (the instance methods used by registered Definitions). Per user feedback, the delivery-type loop's key construction must NOT be a parallel implementation. Extract the pure formulas into a small utility; `AbstractOrderOptionDefinition` delegates to it (no behavior change for Definitions); the view loop calls the utility directly (works for any capability-emitted delivery type, not just those with a pre-declared `ALLOW_*` const).

- [ ] **Step 1: Create `src/Base/Support/SettingKey.php`**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

/**
 * Pure-string utilities for constructing setting attribute keys.
 *
 * Used by both `AbstractOrderOptionDefinition` (for registered options) and
 * capability-driven views (e.g. `CarrierSettingsItemView` for delivery types).
 * Centralising the formulas here keeps the SDK-aligned attribute-naming
 * convention in exactly one place.
 *
 * The four prefixes mirror the four-axis settings model:
 *   - `allow*`  — "allow X at checkout" toggle on `CarrierSettings`
 *   - `price*`  — surcharge for option X on `CarrierSettings`
 *   - `export*` — both carrier-level (`CarrierSettings`) and product-level
 *                 (`ProductSettings`) "export X with the shipment"
 *   - `priceDeliveryType*` — surcharge per delivery type on `CarrierSettings`
 */
final class SettingKey
{
    public static function allow(string $key): string
    {
        return 'allow' . ucfirst($key);
    }

    public static function price(string $key): string
    {
        return 'price' . ucfirst($key);
    }

    public static function export(string $key): string
    {
        return 'export' . ucfirst($key);
    }

    public static function priceDeliveryType(string $key): string
    {
        return 'priceDeliveryType' . ucfirst($key);
    }
}
```

- [ ] **Step 2: Make `AbstractOrderOptionDefinition` delegate to `SettingKey`**

In `src/App/Options/Definition/AbstractOrderOptionDefinition.php`, change the four key-deriving methods to call the utility instead of inlining the formula:

Find:

```php
public function getCarrierSettingsKey(): ?string
{
    $key = $this->getShipmentOptionsKey();

    return $key ? 'export' . ucfirst($key) : null;
}
```

Replace with:

```php
public function getCarrierSettingsKey(): ?string
{
    $key = $this->getShipmentOptionsKey();

    return $key ? SettingKey::export($key) : null;
}
```

Repeat for `getAllowSettingsKey()` → `SettingKey::allow($key)` and `getPriceSettingsKey()` → `SettingKey::price($key)`.

(`getProductSettingsKey()` already delegates to `getCarrierSettingsKey()` per the current default — no change needed there.)

Add `use MyParcelNL\Pdk\Base\Support\SettingKey;` to the top.

- [ ] **Step 3: Add unit tests for `SettingKey`**

Create `tests/Unit/Base/Support/SettingKeyTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\SettingKey;

it('builds allow keys', function () {
    expect(SettingKey::allow('signature'))->toBe('allowSignature');
    expect(SettingKey::allow('priorityDelivery'))->toBe('allowPriorityDelivery');
});

it('builds price keys', function () {
    expect(SettingKey::price('signature'))->toBe('priceSignature');
});

it('builds export keys', function () {
    expect(SettingKey::export('insurance'))->toBe('exportInsurance');
});

it('builds priceDeliveryType keys', function () {
    expect(SettingKey::priceDeliveryType('evening'))->toBe('priceDeliveryTypeEvening');
    expect(SettingKey::priceDeliveryType('sameDay'))->toBe('priceDeliveryTypeSameDay');
});
```

Run:

```bash
docker compose run --rm php composer test -- --filter=SettingKey 2>&1 | tail -20
```

Expected: pass.

Also run the Definition tests to confirm the delegation is semantically equivalent:

```bash
docker compose run --rm php composer test -- --filter='OrderOptionDefinition|AbstractOrderOptionDefinition' 2>&1 | tail -20
```

Expected: pass.

- [ ] **Step 4: Update the loop in `CarrierSettingsItemView`**

In `src/Frontend/View/CarrierSettingsItemView.php`, find:

```php
        foreach ($this->carrier->deliveryTypes as $deliveryType) {
            // Ignore unsupported types and pickup (pickup is handled in a separate section in getDeliveryOptionsFields())
            if ($deliveryType === RefTypesDeliveryTypeV2::PICKUP) {
                continue;
            }

            // @TODO: in the future, make this fully dynamic by also allowing custom delivery types from carriers and not relying on predefined constants
            if (\defined(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType))) {
                $typeAllowedSetting = constant(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType));
            } else {
                continue;
            }

            if (\defined(CarrierSettings::class . "::PRICE_DELIVERY_TYPE_" . strtoupper($deliveryType))) {
                $typePriceSetting = constant(CarrierSettings::class . "::PRICE_DELIVERY_TYPE_" . strtoupper($deliveryType));
            } else {
                continue;
            }
```

Replace with:

```php
        foreach ($this->carrier->deliveryTypes as $deliveryType) {
            // Pickup is handled in a separate section in getDeliveryOptionsFields().
            if ($deliveryType === RefTypesDeliveryTypeV2::PICKUP) {
                continue;
            }

            // Construct the keys via the shared SettingKey utility so any capability-emitted
            // delivery type works without needing a pre-declared ALLOW_*/PRICE_* constant.
            $typeAllowedSetting = SettingKey::allow($deliveryType);
            $typePriceSetting   = SettingKey::priceDeliveryType($deliveryType);
```

Add `use MyParcelNL\Pdk\Base\Support\SettingKey;` to the top. The `@TODO` comment is removed since the work it described is now done.

- [ ] **Step 4: Verify the migration didn't break the view**

```bash
docker compose run --rm php composer test -- --filter=CarrierSettings 2>&1 | tail -30
```

Expected: pass. If a snapshot test changes shape (it shouldn't — the setting key strings are identical), regenerate via `yarn test:unit:snapshot` and inspect the diff with the user before accepting.

- [ ] **Step 5: PHPStan**

```bash
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: zero errors.

- [ ] **Step 6: No commit yet.**

---

## Task 5: Plugin scan + coordination

**Files:** Plugin repositories (NOT the PDK).

- [ ] **Step 1: Scan plugins for hits to the three removed/changed symbols**

```bash
rg -n '\bAccount::FEATURE_ORDER_NOTES\b|\bDIGITAL_STAMP_RANGES\b' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

For any hits:

- `Account::FEATURE_ORDER_NOTES` → migrate to `PdkAccountFeaturesService::FEATURE_ORDER_NOTES` in a coordinated plugin PR.
- `DIGITAL_STAMP_RANGES` → migrate to `Pdk::get('digitalStampRanges')` in a coordinated plugin PR.

XCut B-4 is purely internal to `CarrierSettingsItemView` — no plugin impact expected, but verify:

```bash
rg -n '\bdefined\(CarrierSettings::class \. "::ALLOW_' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

Expected: no output. (Plugins shouldn't be replicating the same `defined()` pattern.)

- [ ] **Step 2: For any required plugin changes, apply on coordinated branches**

Same workflow as previous plans: branch in each plugin repo, apply the changes, surface diffs to the user before opening PRs.

- [ ] **Step 3: No PDK commit on this step.**

---

## Task 6: Final verification + commit

**Files:** Stage all PDK changes.

- [ ] **Step 1: Full sweep**

```bash
rg '\bAccount::FEATURE_ORDER_NOTES\b' src/ tests/ --type=php
rg '\bDIGITAL_STAMP_RANGES\b' src/ tests/ --type=php
rg '\\defined\(CarrierSettings::class' src/ --type=php
```

Expected: no output on any. (The last one verifies the dynamic-key loop change took effect.)

- [ ] **Step 2: Tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log; echo "exit: $?"
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 3: Show the diff for user review**

```bash
git diff --staged --stat
git status --short
```

Wait for user approval.

- [ ] **Step 4: Commit (only after approval)**

```bash
git commit -m "$(cat <<'EOF'
refactor(cleanup): three cross-cutting capability-migration cleanups

Three independent items bundled because each is small:

1. Account::FEATURE_ORDER_NOTES: the hand-declared PDK const was
   @deprecated in favor of the SDK's IamApi\Model\Feature::ORDER_NOTES.
   Callers migrated to PdkAccountFeaturesService::FEATURE_ORDER_NOTES
   (which already aliases the SDK enum); the Account-level const is
   deleted.

2. WeightServiceInterface::DIGITAL_STAMP_RANGES: @deprecated for
   Pdk::get('digitalStampRanges'); zero callers; deleted.

3. CarrierSettingsItemView delivery-type loop: stop relying on
   defined(CarrierSettings::ALLOW_*) for each capability-emitted
   delivery type. Extracts a new SettingKey utility
   (src/Base/Support/SettingKey.php) holding the 'allow' . ucfirst,
   'price' . ucfirst, 'export' . ucfirst, and
   'priceDeliveryType' . ucfirst formulas. AbstractOrderOptionDefinition
   delegates its key-deriving methods to the utility (no behavior
   change). The view loop calls the utility directly, so any
   capability-emitted delivery type works without needing a pre-declared
   ALLOW_*/PRICE_* constant. The @TODO comment is removed.

Plugin coordination: required only if plugin scans surface plugin-side
callers of the deleted Account/WeightServiceInterface consts.

Audit reference:
docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Cross-cutting B-2, B-3, B-4).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 5: Verify commit**

```bash
git log -1 --stat
```

Expected: 4-5 PDK files modified — `Account.php`, `PostOrderNotesAction.php`, `WeightServiceInterface.php`, `CarrierSettingsItemView.php`, `CarrierSettings.php`, plus 1 test file.

- [ ] **Step 6: Plan complete.**

---

## Roll-back

```bash
git revert HEAD
```

Restores all three items. Plugin PRs (if applied) must be reverted separately.

---

## Why this is safe

- B-2: the SDK alias has the same string value (`'ORDER_NOTES'`); migration is mechanically equivalent. Callers were already required to use either the SDK enum or the wrapper service.
- B-3: 0 callers in PDK; plugin scan determines plugin readiness before deletion.
- B-4: the dynamic key construction produces strings identical to the old const values (`'allowEvening'` etc.); behavior is preserved for delivery types that already had constants, AND the loop now also handles delivery types that didn't have a pre-declared const (the silently-skipped case becomes a successfully-rendered setting).

---

## Open questions

- **B-4 fallback for unregistered delivery types.** If the dynamic key construction produces `'allowTwilight'` but no `'allowTwilight'` attribute is registered on `CarrierSettings`, the setting won't have a UI effect (writing to it just sets a dynamic property). Two responses:
  - (a) Leave it — the constructed key has no UI effect; the dynamic loop simply renders a field that doesn't persist anywhere. Surface to the user during execution if this is a concern.
  - (b) Defensive: check whether `array_key_exists($key, $carrierSettings->attributes)` and skip if not — but that re-introduces an existence check, just on attributes instead of constants. Goes against the "fully dynamic" direction.
  - Recommend (a) unless the user pushes back.
- **`SettingKey` utility location.** Plan places it at `src/Base/Support/SettingKey.php`. If the codebase already has a similar utility (e.g. on `Str`), use that instead. Surface during Task 4 Step 1 if a better home exists.
- **Plugin migration timing.** If any plugin references either `Account::FEATURE_ORDER_NOTES` or `DIGITAL_STAMP_RANGES`, the plugin PRs ship before/with the PDK PR.
