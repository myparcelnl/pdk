# Settings + Shipment const migration: drop 53 @deprecated consts — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Delete the 53 `@deprecated` constants across `CarrierSettings` (24), `ProductSettings` (11), and `ShipmentOptions` (18). For the 9 consts with zero references anywhere, delete outright. For the remaining 44, migrate every call site (PDK + both plugins) to derive the key from the corresponding `OptionDefinition`'s getter (or to the alias target for the 2 alias-only deprecations), then delete the consts. After this plan, each option's key has exactly one source of truth: its `OptionDefinition`.

**Architecture:** Today each option's attribute name exists in multiple places — a `@deprecated` const on a settings/shipment model and a derived getter on its `OptionDefinition`. Callers should rely on the Definition; the const is residual. The 2 alias-only deprecations (`ALLOW_PICKUP_LOCATIONS`, `ALLOW_DELIVERY_TYPE_EXPRESS`) point to other surviving consts, not to a Definition — callers migrate to the alias target.

**Scope correction (important):** The original audit findings doc undercounted. The actual scope is 53 deprecated consts (not 36 as the master findings doc states). After this plan ships, the findings doc should be amended to reflect 53. See "Open questions" for the proposed correction.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep. Touches PDK src/, PDK tests, AND both plugins (PrestaShop + WooCommerce) — coordinated plugin PRs required.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Schema A-7..A-14 + B-2 + Shipment A-5 + B-3, **expanded** during plan drafting to cover ProductSettings + the additional `ALLOW_*` consts the audit missed.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`. **Sequencing note:** if `CarrierSettingsItemView` is touched by both this plan and the Validation/CarrierSchema dissolution, run this AFTER that one to avoid double-touching the view (both plans replace `EXPORT_INSURANCE` calls there).

---

## Constants in scope (all 53)

### `CarrierSettings` (24 deprecated consts)

**Definition-derived (22, migrate then delete):**

| Const                      | Value                      | Migration target                                      | State                  |
| -------------------------- | -------------------------- | ----------------------------------------------------- | ---------------------- |
| `ALLOW_ONLY_RECIPIENT`     | `'allowOnlyRecipient'`     | `OnlyRecipientDefinition::getAllowSettingsKey()`      | alive                  |
| `ALLOW_PRIORITY_DELIVERY`  | `'allowPriorityDelivery'`  | `PriorityDeliveryDefinition::getAllowSettingsKey()`   | alive                  |
| `ALLOW_SAME_DAY_DELIVERY`  | `'allowSameDayDelivery'`   | `SameDayDeliveryDefinition::getAllowSettingsKey()`    | alive                  |
| `ALLOW_SATURDAY_DELIVERY`  | `'allowSaturdayDelivery'`  | `SaturdayDeliveryDefinition::getAllowSettingsKey()`   | alive                  |
| `ALLOW_SIGNATURE`          | `'allowSignature'`         | `SignatureDefinition::getAllowSettingsKey()`          | alive                  |
| `EXPORT_AGE_CHECK`         | `'exportAgeCheck'`         | `AgeCheckDefinition::getCarrierSettingsKey()`         | alive                  |
| `EXPORT_HIDE_SENDER`       | `'exportHideSender'`       | `HideSenderDefinition::getCarrierSettingsKey()`       | **dead**               |
| `EXPORT_INSURANCE`         | `'exportInsurance'`        | `InsuranceDefinition::getCarrierSettingsKey()`        | alive (heavy: 17 refs) |
| `EXPORT_LARGE_FORMAT`      | `'exportLargeFormat'`      | `LargeFormatDefinition::getCarrierSettingsKey()`      | alive                  |
| `EXPORT_ONLY_RECIPIENT`    | `'exportOnlyRecipient'`    | `OnlyRecipientDefinition::getCarrierSettingsKey()`    | alive                  |
| `EXPORT_RECEIPT_CODE`      | `'exportReceiptCode'`      | `ReceiptCodeDefinition::getCarrierSettingsKey()`      | **dead**               |
| `EXPORT_RETURN`            | `'exportReturn'`           | `DirectReturnDefinition::getCarrierSettingsKey()`     | alive                  |
| `EXPORT_SIGNATURE`         | `'exportSignature'`        | `SignatureDefinition::getCarrierSettingsKey()`        | alive                  |
| `EXPORT_TRACKED`           | `'exportTracked'`          | `TrackedDefinition::getCarrierSettingsKey()`          | alive                  |
| `EXPORT_COLLECT`           | `'exportCollect'`          | `CollectDefinition::getCarrierSettingsKey()`          | **dead**               |
| `EXPORT_FRESH_FOOD`        | `'exportFreshFood'`        | `FreshFoodDefinition::getCarrierSettingsKey()`        | **dead**               |
| `EXPORT_FROZEN`            | `'exportFrozen'`           | `FrozenDefinition::getCarrierSettingsKey()`           | **dead**               |
| `EXPORT_PRIORITY_DELIVERY` | `'exportPriorityDelivery'` | `PriorityDeliveryDefinition::getCarrierSettingsKey()` | **dead**               |
| `PRICE_ONLY_RECIPIENT`     | `'priceOnlyRecipient'`     | `OnlyRecipientDefinition::getPriceSettingsKey()`      | alive                  |
| `PRICE_SIGNATURE`          | `'priceSignature'`         | `SignatureDefinition::getPriceSettingsKey()`          | alive                  |
| `PRICE_PRIORITY_DELIVERY`  | `'pricePriorityDelivery'`  | `PriorityDeliveryDefinition::getPriceSettingsKey()`   | **dead**               |
| `PRICE_COLLECT`            | `'priceCollect'`           | `CollectDefinition::getPriceSettingsKey()`            | **dead**               |

**Alias-only (2, migrate to alias target then delete):**

| Const                         | Value                        | Migration target                                                               | State |
| ----------------------------- | ---------------------------- | ------------------------------------------------------------------------------ | ----- |
| `ALLOW_PICKUP_LOCATIONS`      | `'allowPickupLocations'`     | `ALLOW_PICKUP_DELIVERY` (same value, surviving const)                          | alive |
| `ALLOW_DELIVERY_TYPE_EXPRESS` | `'allowDeliveryTypeExpress'` | `ALLOW_EXPRESS_DELIVERY` (different value; not a synonym — see Open questions) | alive |

### `ProductSettings` (11 Definition-derived deprecated consts)

| Const                    | Value                    | Migration target                                    | State |
| ------------------------ | ------------------------ | --------------------------------------------------- | ----- |
| `EXPORT_AGE_CHECK`       | `'exportAgeCheck'`       | `AgeCheckDefinition::getProductSettingsKey()`       | alive |
| `EXPORT_HIDE_SENDER`     | `'exportHideSender'`     | `HideSenderDefinition::getProductSettingsKey()`     | alive |
| `EXPORT_INSURANCE`       | `'exportInsurance'`      | `InsuranceDefinition::getProductSettingsKey()`      | alive |
| `EXPORT_LARGE_FORMAT`    | `'exportLargeFormat'`    | `LargeFormatDefinition::getProductSettingsKey()`    | alive |
| `EXPORT_ONLY_RECIPIENT`  | `'exportOnlyRecipient'`  | `OnlyRecipientDefinition::getProductSettingsKey()`  | alive |
| `EXPORT_RETURN`          | `'exportReturn'`         | `DirectReturnDefinition::getProductSettingsKey()`   | alive |
| `EXPORT_SIGNATURE`       | `'exportSignature'`      | `SignatureDefinition::getProductSettingsKey()`      | alive |
| `EXPORT_TRACKED`         | `'exportTracked'`        | `TrackedDefinition::getProductSettingsKey()`        | alive |
| `EXPORT_FRESH_FOOD`      | `'exportFreshFood'`      | `FreshFoodDefinition::getProductSettingsKey()`      | alive |
| `EXPORT_FROZEN`          | `'exportFrozen'`         | `FrozenDefinition::getProductSettingsKey()`         | alive |
| `EXPORT_COOLED_DELIVERY` | `'exportCooledDelivery'` | `CooledDeliveryDefinition::getProductSettingsKey()` | alive |

### `ShipmentOptions` (18 deprecated consts)

**Definition-derived (17, migrate then delete):**

| Const                    | Value                    | Migration target                                          |
| ------------------------ | ------------------------ | --------------------------------------------------------- |
| `INSURANCE`              | `'insurance'`            | `InsuranceDefinition::getShipmentOptionsKey()`            |
| `AGE_CHECK`              | `'ageCheck'`             | `AgeCheckDefinition::getShipmentOptionsKey()`             |
| `DIRECT_RETURN`          | `'return'`               | `DirectReturnDefinition::getShipmentOptionsKey()`         |
| `HIDE_SENDER`            | `'hideSender'`           | `HideSenderDefinition::getShipmentOptionsKey()`           |
| `LARGE_FORMAT`           | `'largeFormat'`          | `LargeFormatDefinition::getShipmentOptionsKey()`          |
| `ONLY_RECIPIENT`         | `'onlyRecipient'`        | `OnlyRecipientDefinition::getShipmentOptionsKey()`        |
| `PRIORITY_DELIVERY`      | `'priorityDelivery'`     | `PriorityDeliveryDefinition::getShipmentOptionsKey()`     |
| `RECEIPT_CODE`           | `'receiptCode'`          | `ReceiptCodeDefinition::getShipmentOptionsKey()`          |
| `SAME_DAY_DELIVERY`      | `'sameDayDelivery'`      | `SameDayDeliveryDefinition::getShipmentOptionsKey()`      |
| `SATURDAY_DELIVERY`      | `'saturdayDelivery'`     | `SaturdayDeliveryDefinition::getShipmentOptionsKey()`     |
| `MONDAY_DELIVERY`        | `'mondayDelivery'`       | (no Definition — see Open questions)                      |
| `SIGNATURE`              | `'signature'`            | `SignatureDefinition::getShipmentOptionsKey()`            |
| `TRACKED`                | `'tracked'`              | `TrackedDefinition::getShipmentOptionsKey()`              |
| `COLLECT`                | `'collect'`              | `CollectDefinition::getShipmentOptionsKey()`              |
| `EXCLUDE_PARCEL_LOCKERS` | `'excludeParcelLockers'` | `ExcludeParcelLockersDefinition::getShipmentOptionsKey()` |
| `FRESH_FOOD`             | `'freshFood'`            | `FreshFoodDefinition::getShipmentOptionsKey()`            |
| `FROZEN`                 | `'frozen'`               | `FrozenDefinition::getShipmentOptionsKey()`               |
| `COOLED_DELIVERY`        | `'cooledDelivery'`       | `CooledDeliveryDefinition::getShipmentOptionsKey()`       |

**Dead (1, delete outright):**

| Const                  | Value           | Notes                        |
| ---------------------- | --------------- | ---------------------------- |
| `ALL_SHIPMENT_OPTIONS` | `[...]` (array) | Schema A-5. 0 refs anywhere. |

### NOT in scope

Sub-attribute consts (no Definition equivalent): `CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT`, `_PRICE_PERCENTAGE`, `_UP_TO`, `_UP_TO_EU`, `_UP_TO_ROW`, `_UP_TO_UNIQUE`, `EXPORT_RETURN_LARGE_FORMAT`, `EXPORT_RETURN_PACKAGE_TYPE`, all `ALLOW_*_DELIVERY` for delivery types (Standard/Evening/Monday/Morning/Express), all `ALLOW_PICKUP_*`, all `PRICE_DELIVERY_TYPE_*`, all `PRICE_PACKAGE_TYPE_*`, and any other non-`@deprecated` const. Verify by reading the source files (Task 1 Step 3-4).

---

## Migration recipe

Per call site, two patterns. Pick consistently per file.

**Pattern A — Inline Definition instantiation** (for files using a key 1-2 times):

```php
// Before:
$settings->get(CarrierSettings::EXPORT_INSURANCE)

// After:
$settings->get((new InsuranceDefinition())->getCarrierSettingsKey())
```

**Pattern B — Hoisted Definition** (when the same key appears 3+ times in a scope):

```php
$insuranceDef = new InsuranceDefinition();
// ...later:
$settings->get($insuranceDef->getCarrierSettingsKey())
$settings->set($insuranceDef->getCarrierSettingsKey(), $value)
```

**Pattern C — Alias migration** (for the 2 alias-only deprecations):

```php
// Before:
CarrierSettings::ALLOW_PICKUP_LOCATIONS

// After:
CarrierSettings::ALLOW_PICKUP_DELIVERY
```

`OptionDefinition` instances are stateless value objects; constructing them inline is cheap. **Do NOT** introduce a new helper class or registry just for this migration — adds an abstraction the audit's "simplification" goal is trying to remove.

Each migration requires the `use` statement for the relevant `OptionDefinition`.

---

## File structure

| File                                                          | Action               | Responsibility                                               |
| ------------------------------------------------------------- | -------------------- | ------------------------------------------------------------ |
| `src/Settings/Model/CarrierSettings.php`                      | Modify               | Delete 24 `@deprecated` consts.                              |
| `src/Settings/Model/ProductSettings.php`                      | Modify               | Delete 11 `@deprecated` consts.                              |
| `src/Shipment/Model/ShipmentOptions.php`                      | Modify               | Delete 18 `@deprecated` consts.                              |
| PDK src/ callers                                              | Modify               | Migrate per recipe. Inventory in Task 2.                     |
| PDK tests/ callers                                            | Modify               | Migrate per recipe.                                          |
| `~/projects/docker-prestashop/modules/myparcelnl/`            | Modify (plugin repo) | Plugin migration; separate plugin PR. **Not in PDK commit.** |
| `~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/` | Modify (plugin repo) | Plugin migration; separate plugin PR. **Not in PDK commit.** |

---

## Task 1: Pre-flight + verify the deprecated-const list

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

- [ ] **Step 3: Confirm exactly 24 deprecated consts on `CarrierSettings`**

```bash
rg -nB1 '@deprecated' src/Settings/Model/CarrierSettings.php | grep -A1 'deprecated' | grep 'public const' | wc -l
```

Expected: `24`. If different, the plan's count drifted — surface to the user.

- [ ] **Step 4: Confirm exactly 11 deprecated consts on `ProductSettings`**

```bash
rg -nB1 '@deprecated' src/Settings/Model/ProductSettings.php | grep -A1 'deprecated' | grep 'public const' | wc -l
```

Expected: `11`.

- [ ] **Step 5: Confirm exactly 18 deprecated consts on `ShipmentOptions`**

```bash
rg -nB1 '@deprecated' src/Shipment/Model/ShipmentOptions.php | grep -A1 'deprecated' | grep 'public const' | wc -l
```

Expected: `18`.

- [ ] **Step 6: Verify the 9 "dead" Definition-derived consts have 0 refs**

The dead set (per the action map):

- CarrierSettings: `EXPORT_HIDE_SENDER`, `EXPORT_RECEIPT_CODE`, `EXPORT_COLLECT`, `EXPORT_FRESH_FOOD`, `EXPORT_FROZEN`, `EXPORT_PRIORITY_DELIVERY`, `PRICE_PRIORITY_DELIVERY`, `PRICE_COLLECT` (8)
- ShipmentOptions: `ALL_SHIPMENT_OPTIONS` (1)

```bash
for c in EXPORT_HIDE_SENDER EXPORT_RECEIPT_CODE EXPORT_COLLECT EXPORT_FRESH_FOOD EXPORT_FROZEN EXPORT_PRIORITY_DELIVERY PRICE_PRIORITY_DELIVERY PRICE_COLLECT; do
  hits=$(rg -c "CarrierSettings::$c\b|self::$c\b|static::$c\b" src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null | awk -F: '{s+=$NF} END {print s+0}')
  echo "CarrierSettings::$c → $hits hits"
done

for c in ALL_SHIPMENT_OPTIONS; do
  hits=$(rg -c "ShipmentOptions::$c\b|self::$c\b|static::$c\b" src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null | awk -F: '{s+=$NF} END {print s+0}')
  echo "ShipmentOptions::$c → $hits hits"
done
```

Expected: all 0. If non-zero, the const is alive — reclassify and update plan before continuing.

- [ ] **Step 7: No commit.**

---

## Task 2: Build the caller inventory

**Files:** Create `/tmp/const-migration-inventory.md` (working file; not committed).

- [ ] **Step 1: Generate the inventory per model**

```bash
{
  echo "# Const migration inventory"
  for model_const in \
      "CarrierSettings::ALLOW_ONLY_RECIPIENT" "CarrierSettings::ALLOW_PRIORITY_DELIVERY" "CarrierSettings::ALLOW_SAME_DAY_DELIVERY" "CarrierSettings::ALLOW_SATURDAY_DELIVERY" "CarrierSettings::ALLOW_SIGNATURE" \
      "CarrierSettings::EXPORT_AGE_CHECK" "CarrierSettings::EXPORT_INSURANCE" "CarrierSettings::EXPORT_LARGE_FORMAT" "CarrierSettings::EXPORT_ONLY_RECIPIENT" "CarrierSettings::EXPORT_RETURN" "CarrierSettings::EXPORT_SIGNATURE" "CarrierSettings::EXPORT_TRACKED" \
      "CarrierSettings::PRICE_ONLY_RECIPIENT" "CarrierSettings::PRICE_SIGNATURE" \
      "CarrierSettings::ALLOW_PICKUP_LOCATIONS" "CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS" \
      "ProductSettings::EXPORT_AGE_CHECK" "ProductSettings::EXPORT_HIDE_SENDER" "ProductSettings::EXPORT_INSURANCE" "ProductSettings::EXPORT_LARGE_FORMAT" "ProductSettings::EXPORT_ONLY_RECIPIENT" "ProductSettings::EXPORT_RETURN" "ProductSettings::EXPORT_SIGNATURE" "ProductSettings::EXPORT_TRACKED" "ProductSettings::EXPORT_FRESH_FOOD" "ProductSettings::EXPORT_FROZEN" "ProductSettings::EXPORT_COOLED_DELIVERY" \
      "ShipmentOptions::INSURANCE" "ShipmentOptions::AGE_CHECK" "ShipmentOptions::DIRECT_RETURN" "ShipmentOptions::HIDE_SENDER" "ShipmentOptions::LARGE_FORMAT" "ShipmentOptions::ONLY_RECIPIENT" "ShipmentOptions::PRIORITY_DELIVERY" "ShipmentOptions::RECEIPT_CODE" "ShipmentOptions::SAME_DAY_DELIVERY" "ShipmentOptions::SATURDAY_DELIVERY" "ShipmentOptions::MONDAY_DELIVERY" "ShipmentOptions::SIGNATURE" "ShipmentOptions::TRACKED" "ShipmentOptions::COLLECT" "ShipmentOptions::EXCLUDE_PARCEL_LOCKERS" "ShipmentOptions::FRESH_FOOD" "ShipmentOptions::FROZEN" "ShipmentOptions::COOLED_DELIVERY"
  do
    echo
    echo "### $model_const"
    rg -n "$model_const\b" src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
  done
} > /tmp/const-migration-inventory.md
wc -l /tmp/const-migration-inventory.md
```

Read the file. Confirm coverage is reasonable.

- [ ] **Step 2: Split inventory into PDK + plugins**

```bash
grep -E '^(src|tests)/' /tmp/const-migration-inventory.md > /tmp/const-migration-pdk.md
grep -E '^/Users/freek.vanrijt/projects/docker-' /tmp/const-migration-inventory.md > /tmp/const-migration-plugins.md
wc -l /tmp/const-migration-pdk.md /tmp/const-migration-plugins.md
```

PDK side goes in this commit. Plugin migrations ship as coordinated plugin PRs (Task 7).

- [ ] **Step 3: No commit.**

---

## Task 3: Migrate PDK src/ callers — CarrierSettings consts

**Files:** Various PDK src/ files identified in Task 2.

Apply Pattern A (inline) or Pattern B (hoisted) per file. Pattern C (alias) for the 2 alias-only consts.

- [ ] **Step 1: Migrate `CarrierSettingsItemView` (likely heaviest single file)**

Per the earlier survey, `EXPORT_INSURANCE` appears 17 times — most are here. Hoist `$insuranceDef = new InsuranceDefinition()` near the top of the relevant method; replace `CarrierSettings::EXPORT_INSURANCE` with `$insuranceDef->getCarrierSettingsKey()` throughout that scope. Handle other consts in this file similarly.

Caveat: sub-attribute consts (`EXPORT_INSURANCE_FROM_AMOUNT` etc.) stay. The migration only touches the deprecated consts in the action map.

If this plan executes after the Validation/CarrierSchema dissolution plan, the file already has heavy edits — pull a fresh copy after the dissolution merges.

- [ ] **Step 2: Migrate other PDK src/ files referencing CarrierSettings deprecated consts**

For each file in `/tmp/const-migration-pdk.md` matching `CarrierSettings::`, apply the recipe.

For the 2 alias-only consts:

- `CarrierSettings::ALLOW_PICKUP_LOCATIONS` → `CarrierSettings::ALLOW_PICKUP_DELIVERY` (Pattern C — same string value, both consts surviving)
- `CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS` → `CarrierSettings::ALLOW_EXPRESS_DELIVERY` — **CAVEAT:** these have different string values (`'allowDeliveryTypeExpress'` vs `'allowExpressDelivery'`). The @deprecated comment says "use ALLOW_EXPRESS_DELIVERY instead", but if any caller depends on the literal value, simply replacing the const reference is a behavior change. Verify each caller's intent before swapping. Surface ambiguous cases to the user.

- [ ] **Step 3: Verify**

```bash
for c in ALLOW_ONLY_RECIPIENT ALLOW_PRIORITY_DELIVERY ALLOW_SAME_DAY_DELIVERY ALLOW_SATURDAY_DELIVERY ALLOW_SIGNATURE EXPORT_AGE_CHECK EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED PRICE_ONLY_RECIPIENT PRICE_SIGNATURE ALLOW_PICKUP_LOCATIONS ALLOW_DELIVERY_TYPE_EXPRESS; do
  rg -q "CarrierSettings::$c\b" src/ && echo "LEAK: CarrierSettings::$c"
done
```

Expected: no "LEAK:" lines. (Tests will be done in Task 5.)

- [ ] **Step 4: Run tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tail -30
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 5: No commit yet.**

---

## Task 4: Migrate PDK src/ callers — ProductSettings consts

**Files:** Various PDK src/ files referencing `ProductSettings::EXPORT_*` deprecated consts.

- [ ] **Step 1: Migrate each file per Pattern A/B**

11 consts; `(new XDefinition())->getProductSettingsKey()` for each.

- [ ] **Step 2: Verify**

```bash
for c in EXPORT_AGE_CHECK EXPORT_HIDE_SENDER EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED EXPORT_FRESH_FOOD EXPORT_FROZEN EXPORT_COOLED_DELIVERY; do
  rg -q "ProductSettings::$c\b" src/ && echo "LEAK: ProductSettings::$c"
done
```

Expected: no "LEAK:" lines.

```bash
docker compose run --rm php composer test 2>&1 | tail -20
```

Expected: pass.

- [ ] **Step 3: No commit yet.**

---

## Task 5: Migrate PDK src/ callers — ShipmentOptions consts

**Files:** Various PDK src/ files referencing `ShipmentOptions::*` deprecated consts.

- [ ] **Step 1: Migrate each file**

17 consts; `(new XDefinition())->getShipmentOptionsKey()` for each.

`ShipmentOptions::MONDAY_DELIVERY` has no Definition target (no `MondayDeliveryDefinition`). Inspect its 1 self-ref site and decide:

- If the self-ref is just the const definition referencing itself, the const can be deleted as dead.
- If it's an internal use (e.g. in `ShipmentOptions::ALL_SHIPMENT_OPTIONS`-style array), replace with the literal string `'mondayDelivery'` and surface to the user — the const may need to migrate to a non-deprecated state or be removed entirely.

- [ ] **Step 2: Verify**

```bash
for c in INSURANCE AGE_CHECK DIRECT_RETURN HIDE_SENDER LARGE_FORMAT ONLY_RECIPIENT PRIORITY_DELIVERY RECEIPT_CODE SAME_DAY_DELIVERY SATURDAY_DELIVERY MONDAY_DELIVERY SIGNATURE TRACKED COLLECT EXCLUDE_PARCEL_LOCKERS FRESH_FOOD FROZEN COOLED_DELIVERY ALL_SHIPMENT_OPTIONS; do
  rg -q "ShipmentOptions::$c\b" src/ && echo "LEAK: ShipmentOptions::$c"
done
```

Expected: no "LEAK:" lines.

```bash
docker compose run --rm php composer test 2>&1 | tail -20
```

Expected: pass.

- [ ] **Step 3: No commit yet.**

---

## Task 6: Migrate PDK tests/ callers

**Files:** Various PDK tests/ files identified in Task 2.

Tests typically use the consts in dataset rows, factory builders, or assertions. Same migration recipe. For tests that compare against expected key strings (e.g. `'exportInsurance'`), the SOURCE-OF-INPUT changes from const to Definition; the assertion stays.

- [ ] **Step 1: Migrate test files per the inventory**

- [ ] **Step 2: Verify**

```bash
for c in ALLOW_ONLY_RECIPIENT ALLOW_PRIORITY_DELIVERY ALLOW_SAME_DAY_DELIVERY ALLOW_SATURDAY_DELIVERY ALLOW_SIGNATURE EXPORT_AGE_CHECK EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED PRICE_ONLY_RECIPIENT PRICE_SIGNATURE ALLOW_PICKUP_LOCATIONS ALLOW_DELIVERY_TYPE_EXPRESS; do
  rg -q "CarrierSettings::$c\b" tests/ && echo "LEAK (tests): CarrierSettings::$c"
done
for c in EXPORT_AGE_CHECK EXPORT_HIDE_SENDER EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED EXPORT_FRESH_FOOD EXPORT_FROZEN EXPORT_COOLED_DELIVERY; do
  rg -q "ProductSettings::$c\b" tests/ && echo "LEAK (tests): ProductSettings::$c"
done
for c in INSURANCE AGE_CHECK DIRECT_RETURN HIDE_SENDER LARGE_FORMAT ONLY_RECIPIENT PRIORITY_DELIVERY RECEIPT_CODE SAME_DAY_DELIVERY SATURDAY_DELIVERY MONDAY_DELIVERY SIGNATURE TRACKED COLLECT EXCLUDE_PARCEL_LOCKERS FRESH_FOOD FROZEN COOLED_DELIVERY ALL_SHIPMENT_OPTIONS; do
  rg -q "ShipmentOptions::$c\b" tests/ && echo "LEAK (tests): ShipmentOptions::$c"
done
```

Expected: no "LEAK:" lines.

- [ ] **Step 3: Run tests**

```bash
docker compose run --rm php composer test 2>&1 | tail -30
```

Expected: pass.

- [ ] **Step 4: No commit yet.**

---

## Task 7: PrestaShop + WooCommerce plugin coordination

**Files:** Plugin repositories (NOT the PDK).

- [ ] **Step 1: Show plugin inventory to the user**

```bash
cat /tmp/const-migration-plugins.md | head -200
wc -l /tmp/const-migration-plugins.md
```

- [ ] **Step 2: For each plugin, run the migration recipe on a coordinated branch**

PrestaShop:

```bash
cd ~/projects/docker-prestashop/modules/myparcelnl/
git checkout -b chore/deprecated-const-migration
# Apply recipe across files in the plugin inventory matching this path
```

WooCommerce:

```bash
cd ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/
git checkout -b chore/deprecated-const-migration
# Same
```

- [ ] **Step 3: Surface the plugin PR drafts to the user**

Push the branches; share the diff with the user before opening PRs (per global "never post to GitHub without asking").

- [ ] **Step 4: PDK side: no commit until plugin coordination is confirmed**

---

## Task 8: Delete the 53 deprecated consts

**Files:**

- Modify: `src/Settings/Model/CarrierSettings.php` (delete 24 consts + docblocks)
- Modify: `src/Settings/Model/ProductSettings.php` (delete 11 consts + docblocks)
- Modify: `src/Shipment/Model/ShipmentOptions.php` (delete 18 consts + docblocks)

- [ ] **Step 1: Delete from CarrierSettings**

Delete the 24 deprecated consts listed in the action map. **Do not** delete any const not in the map (sub-attribute consts and delivery-type/package-type consts stay).

- [ ] **Step 2: Delete from ProductSettings**

Delete the 11 deprecated consts.

- [ ] **Step 3: Delete from ShipmentOptions**

Delete the 18 deprecated consts. Also delete the class-level `@TODO: This should be based off of dynamic shipment option names...` comment if present.

- [ ] **Step 4: Run PHPStan**

```bash
docker compose run --rm php composer analyse 2>&1 | tail -30
```

Expected: zero errors. PHPStan should not find any missing constants — we migrated all PDK callers in Tasks 3-6.

- [ ] **Step 5: Run tests**

```bash
docker compose run --rm php composer test 2>&1 | tail -30
```

Expected: tests pass.

- [ ] **Step 6: No commit yet.**

---

## Task 9: Final verification + PDK commit

**Files:** Stage all PDK changes.

- [ ] **Step 1: Full final sweep — no deprecated const survives**

```bash
for c in ALLOW_ONLY_RECIPIENT ALLOW_PRIORITY_DELIVERY ALLOW_SAME_DAY_DELIVERY ALLOW_SATURDAY_DELIVERY ALLOW_SIGNATURE EXPORT_AGE_CHECK EXPORT_HIDE_SENDER EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RECEIPT_CODE EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED EXPORT_COLLECT EXPORT_FRESH_FOOD EXPORT_FROZEN EXPORT_PRIORITY_DELIVERY PRICE_ONLY_RECIPIENT PRICE_SIGNATURE PRICE_PRIORITY_DELIVERY PRICE_COLLECT ALLOW_PICKUP_LOCATIONS ALLOW_DELIVERY_TYPE_EXPRESS; do
  rg -q "CarrierSettings::$c\b" src/ tests/ && echo "LEAK: CarrierSettings::$c"
done
for c in EXPORT_AGE_CHECK EXPORT_HIDE_SENDER EXPORT_INSURANCE EXPORT_LARGE_FORMAT EXPORT_ONLY_RECIPIENT EXPORT_RETURN EXPORT_SIGNATURE EXPORT_TRACKED EXPORT_FRESH_FOOD EXPORT_FROZEN EXPORT_COOLED_DELIVERY; do
  rg -q "ProductSettings::$c\b" src/ tests/ && echo "LEAK: ProductSettings::$c"
done
for c in INSURANCE AGE_CHECK DIRECT_RETURN HIDE_SENDER LARGE_FORMAT ONLY_RECIPIENT PRIORITY_DELIVERY RECEIPT_CODE SAME_DAY_DELIVERY SATURDAY_DELIVERY MONDAY_DELIVERY SIGNATURE TRACKED COLLECT EXCLUDE_PARCEL_LOCKERS FRESH_FOOD FROZEN COOLED_DELIVERY ALL_SHIPMENT_OPTIONS; do
  rg -q "ShipmentOptions::$c\b" src/ tests/ && echo "LEAK: ShipmentOptions::$c"
done
```

Expected: no "LEAK:" output.

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
refactor(options): drop 53 deprecated CarrierSettings + ProductSettings + ShipmentOptions consts

Removes 24 deprecated consts on CarrierSettings, 11 on ProductSettings,
and 18 on ShipmentOptions — 53 total. Of these, 9 had zero references
anywhere and are deleted outright; the remaining 44 are migrated at every
call site (PDK + both plugins) to derive the key string from the
corresponding OptionDefinition (or the alias target for the 2 alias-only
deprecations), then deleted.

After this change there is exactly one source of truth for each option's
attribute name: the OptionDefinition class. The deprecated consts were
residual hardcoded duplicates left over from the v4-capabilities migration.

Scope correction: the audit's original count was 36; the actual count is
53 (4 ALLOW_* consts on CarrierSettings the audit missed, plus 11 EXPORT_*
consts on ProductSettings the audit missed entirely, plus 2 alias-only
deprecations).

Plugin coordination: equivalent PRs land in docker-prestashop and
docker-wordpress; releases must ship together to avoid breaking plugin
builds against a PDK that no longer exposes the consts.

Audit references:
docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Schema A-7..A-14 + B-2 + Shipment A-5 + B-3, expanded during planning to
cover ProductSettings).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 5: Verify commit**

```bash
git log -1 --stat
```

Expected: 3 settings models modified (the const-bearing files), many src/tests caller files modified.

- [ ] **Step 6: Plan complete.**

---

## Roll-back

```bash
git revert HEAD
```

Restores all 53 deprecated consts and reverts every caller migration. **Plugin PRs must also be reverted separately if applied.**

---

## Why this is safe

- The migration recipe is mechanical: const → Definition method. No semantic change at any call site (the resolved string is identical; verified by reading the const value and the Definition's derivation in the abstract).
- The 9 dead consts have zero references everywhere (verified pre-execution).
- Each of the 44 alive consts has a documented migration target.
- Sub-attribute consts (`EXPORT_INSURANCE_FROM_AMOUNT` etc.) are explicitly out of scope and protected by the recipe (it only targets `@deprecated` consts).
- The 2 alias-only deprecations have explicit handling — `ALLOW_PICKUP_LOCATIONS` redirects to `ALLOW_PICKUP_DELIVERY` (same value), and `ALLOW_DELIVERY_TYPE_EXPRESS` redirects to `ALLOW_EXPRESS_DELIVERY` (DIFFERENT value; caller-by-caller intent verification required).
- Plugin coordination is explicit; the PDK PR cannot land independently of the plugin PRs.

---

## Open questions

- **Findings doc correction.** The master findings doc states 36 consts; the actual scope is 53. A small follow-up commit on the audit branch should update the count + add `ProductSettings::EXPORT_*` to the action items. Surface to the user after this plan ships.
- **`ALLOW_DELIVERY_TYPE_EXPRESS` value difference.** The const value (`'allowDeliveryTypeExpress'`) differs from its alias target's value (`'allowExpressDelivery'`). If any caller stores or compares against the literal string, swapping the const reference is a behavior change. During Task 3 Step 2, inspect each caller and surface ambiguous ones to the user.
- **`ShipmentOptions::MONDAY_DELIVERY`** has one self-ref and no Definition. After self-ref migration, the const may be dead. Verify during Task 5 Step 1.
- **Definition instance reuse.** Pattern A creates a fresh Definition per call site. If the inline pattern becomes ugly in a file (e.g. PrestaShop migration files use the const heavily), surface to user — a thin helper or hoisting via DI may be worth introducing, but explicitly out of scope for THIS plan.
- **Plugin release coordination.** Plugin PRs must ship before, or in lockstep with, the PDK PR. Coordinate release tags.
