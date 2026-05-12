# Shipment Models cleanup — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Delete two groups of dead/redundant Shipment-Model artifacts that the v4-capabilities migration left behind:

1. `DeliveryType` + `DeliveryTypeCollection` + their two test factories — orphaned model wrappers; the capabilities-driven flow consumes type _names_ (strings) directly.
2. `PackageType` + (optionally) `PackageTypeCollection` + `PackageTypeFactory` — model wrapper that exists only to carry a string name; replace with a plain string in `WeightServiceInterface`.

**Out of scope (kept per user feedback):**

- `RetailLocationType` — kept as-is. It's the PHP 7.4-era validator-as-enum: `RetailLocation::casts['type']` casts to `RetailLocationType::class`, which the Model framework instantiates during hydration, invoking the constructor's `ALL_TYPES` validation. Removing it would lose defensive type safety. The `@todo` to convert it to a backed enum stands for when PHP 7.4 support is dropped.

**Architecture:** Post-v4 the capabilities endpoint emits delivery and package types as strings; PDK models consume those names directly. The standalone `DeliveryType`/`PackageType` model classes are leftovers from the pre-capabilities flow. Deleting `DeliveryType` is mechanical (no callers). `PackageType` requires updating `WeightServiceInterface::addEmptyPackageWeight()` and `::getEffectiveWeight()` signatures from `PackageType $packageType` to `string $packageTypeName` — a contract change requiring coordinated plugin updates (both PrestaShop and WooCommerce implement the interface).

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Shipment Models A-1..A-4 + B-1. (Shipment B-2 — inline `RetailLocationType` — is **closed**: kept for type-safety per user.)

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`. No hard dependencies on other plans.

---

## Scope

### Group 1: `DeliveryType` family (4 files, all delete)

| File                                                                    | Action | Notes                                                            |
| ----------------------------------------------------------------------- | ------ | ---------------------------------------------------------------- |
| `src/Shipment/Model/DeliveryType.php`                                   | Delete | Empty model (`id` + `name`); never instantiated; never returned. |
| `src/Shipment/Collection/DeliveryTypeCollection.php`                    | Delete | Empty collection.                                                |
| `tests/factories/Shipment/Model/DeliveryTypeFactory.php`                | Delete | Orphaned test factory; 0 refs in tests.                          |
| `tests/factories/Shipment/Collection/DeliveryTypeCollectionFactory.php` | Delete | Orphaned test factory.                                           |

Plugin scan confirms 0 references to `DeliveryType` / `DeliveryTypeCollection` (the WC test's `withDeliveryType()` operates on a `DeliveryOptions::DELIVERY_TYPE_MORNING_NAME` string, not the model class).

### Group 2: `PackageType` family

| File                                                                                             | Action                | Notes                                                                                                                              |
| ------------------------------------------------------------------------------------------------ | --------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `src/Shipment/Model/PackageType.php`                                                             | Delete (after Step 6) | Wrapper model used only to carry a string name to `WeightService`.                                                                 |
| `src/Shipment/Collection/PackageTypeCollection.php`                                              | Delete if exists      | Verify in Task 6; may or may not exist.                                                                                            |
| `tests/factories/Shipment/Model/PackageTypeFactory.php`                                          | Delete                | Test factory for the model.                                                                                                        |
| `src/Base/Contract/WeightServiceInterface.php`                                                   | Modify                | Change two method signatures: `PackageType $packageType` → `string $packageTypeName`. **Breaking change for plugin implementers.** |
| `src/Base/Service/WeightService.php`                                                             | Modify                | Match new signatures. Update `getEmptyWeightForPackageType(string)` (private) and the two public methods.                          |
| `src/App/Order/Calculator/General/WeightCalculator.php`                                          | Modify                | Replace `new PackageType(['name' => $name])` with the raw string.                                                                  |
| `src/App/Order/Calculator/General/CapabilitiesPackageTypeCalculator.php`                         | Modify                | Same.                                                                                                                              |
| `src/App/Cart/Service/CartCalculationService.php`                                                | Modify                | Same.                                                                                                                              |
| `~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/src/Pdk/Service/WcWeightService.php` | Modify (plugin repo)  | Update `addEmptyPackageWeight` + `getEffectiveWeight` signatures. **Cross-repo plugin PR.**                                        |
| `~/projects/docker-prestashop/modules/myparcelnl/src/Pdk/Base/Service/PsWeightService.php`       | Modify (plugin repo)  | Same. **Cross-repo plugin PR.**                                                                                                    |

### Out of scope

- `DropOffDay` / `DropOffDayCollection` — alive and specialized (Shipment B-4); leave alone.
- `RetailLocationType` (Shipment B-2) — keep as the PHP 7.4 validator-as-enum.
- `ShipmentOptions` consts (Shipment A-5 + B-3) — covered in the separate Settings + Shipment const migration plan.
- SDK V1↔V2 type mapping — INT-1441 covers.

---

## Task 1: Pre-flight + verify the assumptions

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

- [ ] **Step 3: Verify DeliveryType has 0 production refs**

```bash
rg -l '\bDeliveryType\b' src/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Expected: only `src/Shipment/Model/DeliveryType.php` + `src/Shipment/Collection/DeliveryTypeCollection.php` + the two test factories under `tests/factories/`. Any other hit must be investigated — surface to user before deleting.

- [ ] **Step 4: Verify PackageType caller inventory**

```bash
rg -n 'new PackageType\(' src/ --type=php
```

Expected: exactly 3 sites (file paths listed in the Group 2 file structure table). If more or fewer surface, update the plan before continuing.

```bash
rg -n 'PackageType\b' src/Base/Service/WeightService.php src/Base/Contract/WeightServiceInterface.php
```

Expected: both files reference `PackageType` in signatures + bodies + use statements.

- [ ] **Step 5: Verify WeightServiceInterface implementers**

```bash
rg -l 'implements.*WeightServiceInterface' src/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

Expected exactly:

- `src/Base/Service/WeightService.php` (PDK default)
- `~/projects/docker-wordpress/.../WcWeightService.php`
- `~/projects/docker-prestashop/.../PsWeightService.php`

Any additional implementer → surface to the user before proceeding.

- [ ] **Step 6: No commit.**

---

## Task 2: Delete the DeliveryType family

**Files:**

- Delete: `src/Shipment/Model/DeliveryType.php`
- Delete: `src/Shipment/Collection/DeliveryTypeCollection.php`
- Delete: `tests/factories/Shipment/Model/DeliveryTypeFactory.php`
- Delete: `tests/factories/Shipment/Collection/DeliveryTypeCollectionFactory.php`

- [ ] **Step 1: Delete the four files**

```bash
git rm src/Shipment/Model/DeliveryType.php \
       src/Shipment/Collection/DeliveryTypeCollection.php \
       tests/factories/Shipment/Model/DeliveryTypeFactory.php \
       tests/factories/Shipment/Collection/DeliveryTypeCollectionFactory.php
```

- [ ] **Step 2: Verify no remaining `\bDeliveryType\b` references in PDK**

```bash
rg '\bDeliveryType\b' src/ tests/ --type=php
```

Expected: no output. (Identifiers like `DeliveryTypeCalculator`, `DeliveryTypeV2`, `DELIVERY_TYPE_*_NAME` are different and won't match `\bDeliveryType\b` exactly — but verify the rg result really is empty.)

- [ ] **Step 3: Run tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 4: No commit yet.**

---

## Task 3: Update `WeightService` + `WeightServiceInterface` signatures

**Files:**

- Modify: `src/Base/Contract/WeightServiceInterface.php`
- Modify: `src/Base/Service/WeightService.php`

- [ ] **Step 1: Update the interface**

In `src/Base/Contract/WeightServiceInterface.php`:

Find:

```php
public function addEmptyPackageWeight(int $weight, PackageType $packageType): int;
```

Replace with:

```php
public function addEmptyPackageWeight(int $weight, string $packageTypeName): int;
```

Find:

```php
public function getEffectiveWeight(PdkPhysicalProperties $physicalProperties, PackageType $packageType): int;
```

Replace with:

```php
public function getEffectiveWeight(PdkPhysicalProperties $physicalProperties, string $packageTypeName): int;
```

Update both docblocks: `\MyParcelNL\Pdk\Shipment\Model\PackageType` → `string`. Drop `use MyParcelNL\Pdk\Shipment\Model\PackageType;`.

- [ ] **Step 2: Update `WeightService` to match**

In `src/Base/Service/WeightService.php`:

Find the two public method signatures and update to take `string $packageTypeName` instead of `PackageType $packageType`. Replace any internal `$packageType->name` with `$packageTypeName`.

Find:

```php
private function getEmptyWeightForPackageType(PackageType $packageType): int
{
    $emptyWeightSetting = self::PACKAGE_TYPE_EMPTY_WEIGHT_MAP[$packageType->name] ?? null;
    // ...
}
```

Replace with:

```php
private function getEmptyWeightForPackageType(string $packageTypeName): int
{
    $emptyWeightSetting = self::PACKAGE_TYPE_EMPTY_WEIGHT_MAP[$packageTypeName] ?? null;
    // ...
}
```

Drop `use MyParcelNL\Pdk\Shipment\Model\PackageType;`.

- [ ] **Step 3: Run PHPStan (PDK side)**

```bash
docker compose run --rm php composer analyse 2>&1 | tail -20
```

PDK should be clean. Plugin implementers will fail when their builds run against this PDK — that's expected and addressed in Task 5.

- [ ] **Step 4: No commit yet.**

---

## Task 4: Update PackageType callers in PDK

**Files:**

- Modify: `src/App/Order/Calculator/General/WeightCalculator.php`
- Modify: `src/App/Order/Calculator/General/CapabilitiesPackageTypeCalculator.php`
- Modify: `src/App/Cart/Service/CartCalculationService.php`

- [ ] **Step 1: `WeightCalculator`**

Find:

```php
$weight = Pdk::get(WeightServiceInterface::class)->getEffectiveWeight(
    $physicalProperties,
    new PackageType(['name' => $this->order->deliveryOptions->packageType])
);
```

Replace with:

```php
$weight = Pdk::get(WeightServiceInterface::class)->getEffectiveWeight(
    $physicalProperties,
    $this->order->deliveryOptions->packageType
);
```

Drop `use MyParcelNL\Pdk\Shipment\Model\PackageType;`.

- [ ] **Step 2: `CapabilitiesPackageTypeCalculator`**

Find (around line 174):

```php
new PackageType(['name' => $pdkPackageTypeName])
```

Replace with:

```php
$pdkPackageTypeName
```

Drop the `use MyParcelNL\Pdk\Shipment\Model\PackageType;` import.

- [ ] **Step 3: `CartCalculationService`**

Find (around line 168):

```php
->addEmptyPackageWeight($cart->lines->getTotalWeight(), new PackageType([
    'name' => $packageTypeName,
]))
```

Replace with:

```php
->addEmptyPackageWeight($cart->lines->getTotalWeight(), $packageTypeName)
```

Drop the `use MyParcelNL\Pdk\Shipment\Model\PackageType;` import.

- [ ] **Step 4: Verify no remaining `new PackageType(` in PDK src/**

```bash
rg 'new PackageType\(' src/ --type=php
```

Expected: no output.

- [ ] **Step 5: Run tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: PDK tests pass; PHPStan zero errors.

- [ ] **Step 6: No commit yet.**

---

## Task 5: Coordinate plugin updates for `WeightServiceInterface`

**Files:** Plugin repositories (NOT the PDK).

The interface signature change requires both plugins to update their implementations. Coordinated PRs.

- [ ] **Step 1: Inspect WooCommerce `WcWeightService`**

```bash
sed -n '1,80p' ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/src/Pdk/Service/WcWeightService.php
```

Identify the `addEmptyPackageWeight` and `getEffectiveWeight` overrides.

- [ ] **Step 2: Apply WC plugin update on a coordinated branch**

In the WC plugin repo:

- Update both method signatures: `PackageType $packageType` → `string $packageTypeName`
- Update bodies: `$packageType->name` → `$packageTypeName`
- Drop `use MyParcelNL\Pdk\Shipment\Model\PackageType;`

Branch: `chore/shipment-models-cleanup` in the WC repo. **Do not commit on the PDK side.**

- [ ] **Step 3: Apply PrestaShop plugin update**

Same approach in `~/projects/docker-prestashop/modules/myparcelnl/src/Pdk/Base/Service/PsWeightService.php`.

- [ ] **Step 4: Surface plugin diffs to the user**

Per global rule "never post to GitHub without asking", show the diffs and confirm before opening the plugin PRs.

- [ ] **Step 5: No PDK commit on this step.**

---

## Task 6: Delete the PackageType family

**Files:**

- Delete: `src/Shipment/Model/PackageType.php`
- Delete: `src/Shipment/Collection/PackageTypeCollection.php` (if it exists)
- Delete: `tests/factories/Shipment/Model/PackageTypeFactory.php`

**Important:** Run this only after the plugin PRs are merged (or ready to ship together). The PDK commit deletes the class; if a plugin still has `new PackageType(...)` it breaks at runtime.

- [ ] **Step 1: Confirm plugin readiness with the user**

Plugin PRs ready/merged → proceed. Otherwise, stop and coordinate.

- [ ] **Step 2: Verify `PackageTypeCollection` existence**

```bash
ls src/Shipment/Collection/PackageTypeCollection.php 2>&1
```

If "No such file or directory", drop the corresponding `git rm` below.

- [ ] **Step 3: Delete the files**

```bash
git rm src/Shipment/Model/PackageType.php \
       tests/factories/Shipment/Model/PackageTypeFactory.php
# Conditionally:
git rm src/Shipment/Collection/PackageTypeCollection.php 2>/dev/null || true
```

- [ ] **Step 4: Verify zero remaining `\bPackageType\b` matches in PDK src/**

```bash
rg '\bPackageType\b' src/ --type=php | grep -v 'OrderApiPackageType\|RefShipmentPackageTypeV2'
```

Expected: no output. (SDK-namespaced `PackageType` and `RefShipmentPackageTypeV2` are different and may legitimately survive.)

```bash
rg '\bPackageType\b' tests/ --type=php
```

Expected: no output. If a stray test reference exists, migrate or delete it.

- [ ] **Step 5: Run tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: PDK tests pass; PHPStan zero errors.

- [ ] **Step 6: No commit yet.**

---

## Task 7: Final verification + PDK commit

**Files:** Stage all PDK changes.

- [ ] **Step 1: Full sweep**

```bash
# DeliveryType: gone
rg '\bDeliveryType\b' src/ tests/ --type=php
# PackageType: gone (excluding SDK-namespaced)
rg '\bPackageType\b' src/ tests/ --type=php | grep -v 'OrderApiPackageType\|RefShipmentPackageTypeV2'
```

Expected: no output on either.

- [ ] **Step 2: Tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log; echo "exit: $?"
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 3: Plugin scan**

```bash
rg -l '\b(DeliveryType|PackageType)\b' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

After Task 5's plugin PRs ship: only SDK-namespaced PackageType refs may remain (in the SDK use statements). No PDK PackageType/DeliveryType refs.

- [ ] **Step 4: Show the diff for user review**

```bash
git diff --staged --stat
git status --short
```

Wait for user approval.

- [ ] **Step 5: Commit (only after approval)**

```bash
git commit -m "$(cat <<'EOF'
refactor(shipment-models): drop DeliveryType and PackageType wrappers

Two orthogonal cleanups in the Shipment/Model namespace:

1. Deletes DeliveryType, DeliveryTypeCollection, and their two test
   factories. Post-v4 the capabilities flow consumes delivery-type names
   (strings) directly; the wrapper model is unused.

2. Replaces PackageType model wrapper with a plain string parameter on
   WeightServiceInterface::addEmptyPackageWeight() and ::getEffectiveWeight().
   Three internal callers updated (WeightCalculator,
   CapabilitiesPackageTypeCalculator, CartCalculationService). Deletes
   PackageType, PackageTypeCollection (if present), and PackageTypeFactory.
   Plugin coordination: WcWeightService and PsWeightService implementations
   updated in coordinated plugin PRs.

RetailLocationType is intentionally NOT touched — it's the PHP 7.4
validator-as-enum stand-in (constructor-validated cast on
RetailLocation::type), providing defensive type safety until PHP 7.4
support is dropped and a native enum can replace it.

Audit reference:
docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Shipment A-1..A-4 + B-1).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 6: Verify commit**

```bash
git log -1 --stat
```

Expected: 5-6 deletions (DeliveryType + DeliveryTypeCollection + 2 factories + PackageType + PackageTypeFactory; PackageTypeCollection if it existed), modifications across `Base/Service/`, `Base/Contract/`, `App/Order/Calculator/`, `App/Cart/Service/`.

- [ ] **Step 7: Plan complete.**

---

## Roll-back

```bash
git revert HEAD
```

Restores everything. **Plugin PRs must also be reverted separately if applied** — reverting the PDK commit without the plugins leaves the plugins with `(string $packageTypeName)` signatures against a PDK expecting `(PackageType $packageType)`.

---

## Why this is safe

- DeliveryType deletion is purely additive — 0 callers in PDK src or plugins (verified).
- PackageType replacement is mechanically equivalent: the wrapper carried a string name; we pass the string directly.
- WeightServiceInterface signature change is breaking, but contained: 3 implementers total (PDK default + 2 plugins), all known and updated in coordinated PRs.
- `RetailLocationType` is intentionally preserved — its constructor-validated cast pattern is the PHP 7.4 enum substitute; deleting it would lose defensive type safety.

---

## Open questions

- **Plugin release timing.** Plugin PRs must ship before, or in lockstep with, the PDK PR. Coordinate release tags.
- **`PackageTypeCollection.php` existence.** Verify in Task 6 Step 2 before attempting to delete it. The findings doc mentioned it but it may not exist.
- **Shipment B-2 status in findings doc.** The Shipment B-2 entry ("inline RetailLocationType") is now **closed as not-applicable** — RetailLocationType stays. A small findings-doc amend is appropriate to record this decision (separate commit).
