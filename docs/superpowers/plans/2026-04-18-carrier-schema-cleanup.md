# Remove CarrierSchema and Clean Up Deprecated Carrier Config Surface

> **Execution order — INT-930 migration, step 4 of 4 (final):**
>
> 1. ✅ [Checkout capabilities API](2026-04-14-checkout-capabilities-api.md) — PR #449 / INT-1500 (merged)
> 2. ✅ [Capabilities-driven order calculators](2026-04-15-capabilities-order-calculators.md) — PR #450 / INT-1501 (merged)
> 3. [Admin capabilities context](2026-04-18-admin-capabilities-context.md) — INT-1505 (must be merged before this plan)
> 4. **👉 This plan** — Remove `CarrierSchema` + final dead-code / `@TODO` sweep — INT-1504
>
> This is the final plan in the INT-930 series. Running it last means Task 9's dead-code / `@TODO` sweep can catch anything made redundant by steps 1–3, including the admin-context work.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the INT-930 migration to the capabilities API by removing the deprecated `CarrierSchema` validator, removing deprecated constants on `CarrierSettings` and `ShipmentOptions` models, and making the admin carrier settings view work directly from stored carrier contract definitions and registered option definitions — no reflection, no schema wrapper.

**Architecture:** `CarrierSchema` methods move to the `Carrier` model as direct checks against stored contract-definition data (`canSupportPackageType`, `canSupportDeliveryType`, `canSupportShipmentOption`, `getAllowedInsuranceAmounts`). `OrderOptionDefinitionInterface::validate()` takes `Carrier` instead of `CarrierSchema`. `CarrierSettingsItemView` iterates the carrier's own `packageTypes`, `deliveryTypes`, and `options` (from contract definitions) and looks up matching `OrderOptionDefinition` classes — no reflection on constants. Deprecated constants on `CarrierSettings` / `ShipmentOptions` are removed — their attributes are already populated dynamically from registered definitions.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models (`RefTypesDeliveryTypeV2`, `RefShipmentPackageTypeV2`), no new dependencies

**Jira:** [INT-1504](https://myparcelnl.atlassian.net/browse/INT-1504) (sub-task of [INT-930](https://myparcelnl.atlassian.net/browse/INT-930))

**Branch:** `feat/INT-1504-carrier-schema-cleanup`

**Depends on:** Plans 1 ([PR #449](https://github.com/myparcelnl/pdk/pull/449) / INT-1500), 2 ([PR #450](https://github.com/myparcelnl/pdk/pull/450) / INT-1501), and the admin-capabilities-context plan (INT-1505) must be merged first. This is the final plan in the INT-930 chain — Task 9's dead-code / `@TODO` sweep relies on all prior plans being in place so it can identify everything truly redundant.

**Related research:** [INT-1506](https://myparcelnl.atlassian.net/browse/INT-1506) — Monday delivery is a delivery-options widget config, not a carrier capability. This plan keeps `canHaveMondayDelivery` as a documented PostNL-only exception until that research is resolved.

---

## Project conventions (read first — not optional)

This plan assumes you have no memory from prior sessions. Every convention below was negotiated through code review on Plans 1 and 2 and is enforced in subsequent PR reviews.

### Working environment

- **Platform:** macOS host, Linux container via Docker.
- **PHP support:** PHP 7.4+ compatible. Strict types enforced. Typed properties are OK (PHP 7.4+).
- **Test framework:** Pest v1 only. Do NOT use `describe()` blocks, `arch()`, or `covers()` — those are Pest v2.
- **Running tests:**
  - Full suite: `docker compose run php composer test:unit`
  - Filter: `docker compose run php composer test:unit -- --filter="test name"`
  - Snapshots: `yarn test:unit:snapshot` (runs Prettier on snapshots too)
- **Multi-PHP testing:** `PHP_VERSION=7.4 docker compose run php composer update --no-interaction --no-progress && docker compose run php composer test:unit` — **always run `composer update` first** when switching versions.
- **Static analysis:** `docker compose run php composer analyse` (PHPStan). **Run before committing. No new errors allowed in code you modify.** Pre-existing errors are OK.

### Code style

- **No sentinel values.** Never use `PHP_INT_MAX`, `= -1`, `array_fill_keys(..., PHP_INT_MAX)` as fallback. Use nullable types and explicit null handling.
- **No algorithm jargon in comments or docblocks.** Never write "O(1) lookup", "indexed for constant-time access", etc. Describe what the code does, not its complexity.
- **No unused foreach values.** Replace `foreach ($arr as $key => $_)` with `foreach (array_keys($arr) as $key)`.
- **Comments explain intent, not mechanics.** Keep them concise. Do NOT add docstrings or comments to code you didn't change.
- **Nullable int comparisons:** use `MyParcelNL\Pdk\Base\Support\Utils::compareNullableInts(?int $a, ?int $b): int`. Null is treated as greater than any value.
- **Spaceship operator** `<=>` for comparators, not `$a - $b` (overflow risk).
- **No hardcoded package-type or delivery-type ordering.** Ordering must come from capabilities data (weight limits or similar). Do not rely on PDK ID constants for size relationships.
- **Do not cast SDK enum return types to string** unless PHPStan requires it. The SDK declares enum types (e.g. `RefCapabilitiesSharedCarrierV2`) but returns strings at runtime. When indexing by these values causes PHPStan complaints, add `// @phpstan-ignore-line SDK declares enum type but returns string` instead of casting.

### Architecture conventions

- **Models resolve from settings via attribute getters.** Example: `PdkShippingMethod::getAllowedPackageTypesAttribute()` resolves from `CheckoutSettings::ALLOWED_SHIPPING_METHODS`. Do NOT have services compute values and assign them to model properties externally.
- **DI constructor changes are NOT breaking changes.** The PDK uses auto-wiring (`autowire()` in `config/pdk-services.php`). Platforms don't manually construct services. Do NOT list constructor changes in `BREAKING CHANGE:` footers. Only list removed public API surface (interfaces, public methods, config keys).
- **Carrier settings (`allowX`) take precedence over capabilities.** If the merchant disabled an option, capabilities cannot re-enable it. Resolution order: carrier settings → capabilities (`isRequired`/`requires`/`excludes`) → product/shipment settings.
- **V1 ID constants must be preserved.** `DELIVERY_TYPE_*_ID`, `PACKAGE_TYPE_*_ID`, `DELIVERY_TYPES_NAMES_IDS_MAP`, `PACKAGE_TYPES_NAMES_IDS_MAP`, `DELIVERY_TYPES_V2_MAP`, `PACKAGE_TYPES_V2_MAP`, `getDeliveryTypeId()`, `getPackageTypeId()` — all still used by V1 shipment/return/fulfilment APIs. Do NOT remove them in this plan.
- **Admin carrier settings view uses stored carrier data, not API calls.** The `$this->carrier` object already holds contract-definition data (loaded via `CarrierRepository`). Do NOT make contextual capabilities API calls from the admin settings view — there is no order context.

### Commit conventions

- **Conventional commits:** `feat(scope)!:`, `fix(scope):`, `chore:`, `test:`, `docs:`. The `!` marks a breaking change.
- **Commit body format:**

  ```
  feat(scope): one-line summary

  Optional body explaining why (not what — diff shows what).

  BREAKING CHANGE: only include for actual public API breaks.

  Refs: INT-XXXX

  Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
  ```

- **Always run tests and PHPStan before committing.**
- **Never skip GPG signing.** If commits fail due to 1Password signing errors, report `DONE_WITH_CONCERNS` and let the coordinator commit from a session where 1Password is available.
- **Plan/PR titles use business language**, not internal technical terms. QA uses them for regression testing and customers may see them in changelogs.

### Subagent escalation rule

If tests fail after 2 fix attempts, STOP and report BLOCKED with the exact error output. Do not continue debugging in a loop.

---

## Design decisions

### The view data source is the stored Carrier object

Admin carrier settings are configured globally (not per order), so there is no "shipment context" for contextual capabilities calls. The view uses `$this->carrier`, which is already populated from the contract-definitions API response via `CarrierRepository`. All data the view needs (`packageTypes`, `deliveryTypes`, `options`, `collo`) is on that model.

**Do not call `CapabilitiesValidationService::getCapabilitiesForPackageType()` from this view.** That method is for contextual (order-time) capabilities and would make unnecessary API calls.

### Capabilities-first iteration in the view

The previous approach iterated registered `OrderOptionDefinition` classes and checked each against the carrier via `CarrierSchema::canHaveShipmentOption()`. This plan inverts that: iterate the carrier's `options` and look up a matching `OrderOptionDefinition` by `capabilitiesOptionsKey`. Rationale:

- Matches the mental model "render what the carrier supports"
- Makes it obvious when a carrier exposes an option the PDK doesn't know yet (we can log/skip)
- Avoids reflection on `ALLOW_*` / `PRICE_*` constants

### Delivery types derive keys by convention, not from constants

`$carrier->deliveryTypes` contains V2 names (e.g. `STANDARD_DELIVERY`, `EVENING_DELIVERY`) from `RefTypesDeliveryTypeV2`. For each, derive the carrier settings keys by convention:

- Allow key: `"allow" . Str::studly(strtolower($pdkName))` → `allowStandard`, `allowEvening`
- Price key: `"priceDeliveryType" . Str::studly($pdkName)` → `priceDeliveryTypeStandard`

Map V2 names back to PDK names via `array_flip(DeliveryOptions::DELIVERY_TYPES_V2_MAP)`.

The `CarrierSettings` static constants for these (`ALLOW_STANDARD_DELIVERY`, etc.) are **not deprecated** — they're still the storage keys. The goal is to stop using reflection to look them up; the string-equivalent derived key matches the same settings field through the dynamic attribute system.

### getAllowedInsuranceAmounts belongs on the Carrier model

Currently `CarrierSchema::getAllowedInsuranceAmounts()` reads `$carrier->options->getInsurance()->getInsuredAmount()` and generates tier ranges. Since this derivation uses only `Carrier` data, it becomes a method on the `Carrier` model itself.

### canHaveMondayDelivery stays as a documented exception

Verified against SDK models and MyParcel OpenAPI docs:

- Not in `RefTypesDeliveryTypeV2` enum
- Not in `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2`
- Not in V1 `RefTypesDeliveryType` enum
- Not in `RefShipmentShipmentOptions`
- Only on `GET /delivery_options` as a widget config parameter for PostNL

It's a widget-level config, not a carrier capability. See [INT-1506](https://myparcelnl.atlassian.net/browse/INT-1506) for refinement discussion. This plan keeps the existing PostNL-only check as a helper on the view with an explicit `@TODO` comment referencing INT-1506.

---

## Behavioral test matrix

Tested in **WooCommerce or PrestaShop** admin. End-user behavior should not change — this is internal cleanup.

### Existing behavior (must not regress)

| #   | Scenario                                      | Expected                                                           |
| --- | --------------------------------------------- | ------------------------------------------------------------------ |
| 1   | Open carrier settings page in admin           | All fields render correctly for each enabled carrier               |
| 2   | Capabilities `isRequired` option              | Shown as read-only / always enabled                                |
| 3   | Package type price fields in carrier settings | Only shown for package types the carrier supports                  |
| 4   | Delivery type allow/price fields              | Only shown for delivery types the carrier supports                 |
| 5   | Export order with various carriers            | Insurance amounts correctly calculated (min/max from capabilities) |
| 6   | Order with insurance set                      | Correct tiers available                                            |
| 7   | International mailbox toggle                  | Shown only for carriers that support mailbox                       |
| 8   | V1 shipment creation (POST /shipments)        | Still works — V1 integer IDs sent correctly                        |
| 9   | V1 return shipment creation                   | Still works — V1 integer IDs sent correctly                        |
| 10  | Fulfilment order export                       | Still works — V1 integer IDs sent correctly                        |
| 11  | PostNL carrier settings                       | Monday delivery toggle still visible (documented exception)        |
| 12  | Non-PostNL carrier settings                   | No Monday delivery toggle                                          |

---

## Current state inventory

### CarrierSchema consumers (9 files)

| File                                                           | Methods used                                                                   | Migration target                                                          |
| -------------------------------------------------------------- | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------- |
| `src/Frontend/View/CarrierSettingsItemView.php`                | `canHaveShipmentOption`, `getAllowedInsuranceAmounts`, `canHaveMondayDelivery` | Carrier model methods + view-local Monday exception                       |
| `src/Shipment/Request/PostShipmentsRequest.php`                | multi-collo check                                                              | `$carrier->collo->getMax()` directly                                      |
| `src/Shipment/Request/PostReturnShipmentsRequest.php`          | `hasReturnCapabilities`                                                        | Literal `true` with TODO (schema always returns true)                     |
| `src/App/Order/Calculator/General/InsuranceCalculator.php`     | `getAllowedInsuranceAmounts`                                                   | `$carrier->getAllowedInsuranceAmounts()`                                  |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php` | `canHaveShipmentOption`                                                        | `Carrier::canSupportShipmentOption`                                       |
| `src/App/Options/Definition/FitInMailboxDefinition.php`        | `canBeMailbox`                                                                 | `Carrier::canSupportPackageType(RefShipmentPackageTypeV2::MAILBOX)`       |
| `src/App/Options/Definition/FitInDigitalStampDefinition.php`   | `canBeDigitalStamp`                                                            | `Carrier::canSupportPackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP)` |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`  | Type hint in `validate()` signature                                            | Change to `Carrier`                                                       |

### Deprecated constants to remove

**`src/Settings/Model/CarrierSettings.php`** — all marked `@deprecated` with a replacement definition comment. The `initializeResolvesOptionAttributes()` method (lines 336-379) already populates `$attributes` and `$casts` dynamically from registered `OrderOptionDefinition` classes. The constants are only kept for backward-compatibility reference.

Remove constants:

- `ALLOW_ONLY_RECIPIENT`, `ALLOW_PRIORITY_DELIVERY`, `ALLOW_SAME_DAY_DELIVERY`, `ALLOW_SATURDAY_DELIVERY`, `ALLOW_SIGNATURE`
- `EXPORT_AGE_CHECK`, `EXPORT_HIDE_SENDER`, `EXPORT_INSURANCE`, `EXPORT_LARGE_FORMAT`, `EXPORT_ONLY_RECIPIENT`, `EXPORT_RECEIPT_CODE`, `EXPORT_RETURN`, `EXPORT_SIGNATURE`, `EXPORT_TRACKED`, `EXPORT_COLLECT`, `EXPORT_FRESH_FOOD`, `EXPORT_FROZEN`, `EXPORT_PRIORITY_DELIVERY`
- `PRICE_ONLY_RECIPIENT`, `PRICE_SIGNATURE`, `PRICE_PRIORITY_DELIVERY`, `PRICE_COLLECT`

Also remove their entries from `$attributes` and `$casts` (they're already populated dynamically).

**Keep (still in use):**

- `ID`, `CARRIER_NAME`
- `ALLOW_DELIVERY_OPTIONS`, `ALLOW_STANDARD_DELIVERY`, `ALLOW_EVENING_DELIVERY`, `ALLOW_MONDAY_DELIVERY`, `ALLOW_MORNING_DELIVERY`, `ALLOW_EXPRESS_DELIVERY`, `ALLOW_PICKUP_LOCATIONS`, `ALLOW_PICKUP_DELIVERY`, `ALLOW_DELIVERY_TYPE_EXPRESS` (not deprecated — used for delivery-type UI)
- `CUTOFF_TIME`, `CUTOFF_TIME_SAME_DAY`, `DEFAULT_PACKAGE_TYPE`, `DELIVERY_DAYS_WINDOW`, `DELIVERY_OPTIONS_CUSTOM_CSS`, `DELIVERY_OPTIONS_ENABLED`, `DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS`, `DIGITAL_STAMP_DEFAULT_WEIGHT`, `DROP_OFF_DELAY`, `DROP_OFF_POSSIBILITIES`
- `EXPORT_INSURANCE_FROM_AMOUNT`, `EXPORT_INSURANCE_PRICE_PERCENTAGE`, `EXPORT_INSURANCE_UP_TO`, `EXPORT_INSURANCE_UP_TO_EU`, `EXPORT_INSURANCE_UP_TO_ROW`, `EXPORT_INSURANCE_UP_TO_UNIQUE`, `EXPORT_RETURN_LARGE_FORMAT`, `EXPORT_RETURN_PACKAGE_TYPE`
- All `PRICE_DELIVERY_TYPE_*`, `PRICE_PACKAGE_TYPE_*`
- `ALLOW_INTERNATIONAL_MAILBOX`, `PRICE_INTERNATIONAL_MAILBOX`

**`src/Shipment/Model/ShipmentOptions.php`** — all constants in lines 45-155 marked `@deprecated` and `ALL_SHIPMENT_OPTIONS` array. The `initializeResolvesOptionAttributes()` method populates attributes from definitions. `getAllShipmentOptionKeys()` static method already exists as the replacement for `ALL_SHIPMENT_OPTIONS`.

Keep `LABEL_DESCRIPTION` (not deprecated).

### Constants that must NOT be removed

These are used by the V1 shipment/return/fulfilment APIs:

- `DeliveryOptions::DELIVERY_TYPE_*_ID` (e.g. `DELIVERY_TYPE_MORNING_ID`)
- `DeliveryOptions::PACKAGE_TYPE_*_ID`
- `DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP`
- `DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP`
- `DeliveryOptions::DELIVERY_TYPES_V2_MAP` (used for V2↔legacy conversion)
- `DeliveryOptions::PACKAGE_TYPES_V2_MAP` (same)
- `DeliveryOptions::getDeliveryTypeId()` / `getPackageTypeId()` methods

---

## File Structure

| File                                                           | Action     | Responsibility                                                                                                  |
| -------------------------------------------------------------- | ---------- | --------------------------------------------------------------------------------------------------------------- |
| `src/Carrier/Model/Carrier.php`                                | Modify     | Add `canSupportPackageType`, `canSupportDeliveryType`, `canSupportShipmentOption`, `getAllowedInsuranceAmounts` |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`  | Modify     | Change `validate()` to take `Carrier`                                                                           |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php` | Modify     | Default `validate()` uses `Carrier::canSupportShipmentOption`                                                   |
| `src/App/Options/Definition/FitInMailboxDefinition.php`        | Modify     | `validate()` uses `Carrier::canSupportPackageType`                                                              |
| `src/App/Options/Definition/FitInDigitalStampDefinition.php`   | Modify     | Same pattern as mailbox                                                                                         |
| `src/App/Order/Calculator/General/InsuranceCalculator.php`     | Modify     | Use `$carrier->getAllowedInsuranceAmounts()`                                                                    |
| `src/Shipment/Request/PostShipmentsRequest.php`                | Modify     | Use `$carrier->collo` directly                                                                                  |
| `src/Shipment/Request/PostReturnShipmentsRequest.php`          | Modify     | Remove CarrierSchema, literal `true` with TODO                                                                  |
| `src/Frontend/View/CarrierSettingsItemView.php`                | Modify     | Capabilities-first iteration, no reflection, no CarrierSchema                                                   |
| `src/Settings/Model/CarrierSettings.php`                       | Modify     | Remove deprecated constants + their attribute/cast entries                                                      |
| `src/Shipment/Model/ShipmentOptions.php`                       | Modify     | Remove deprecated constants + `ALL_SHIPMENT_OPTIONS` array                                                      |
| `src/Validation/Validator/CarrierSchema.php`                   | **Delete** | Fully obsolete once all consumers migrated                                                                      |
| `tests/Unit/Carrier/Model/CarrierSupportTest.php`              | Create     | Tests for new `canSupport*` and `getAllowedInsuranceAmounts` methods                                            |

---

### Task 1: Add canSupport\* and getAllowedInsuranceAmounts to Carrier model

Move the boolean checks and insurance tier derivation from `CarrierSchema` onto the `Carrier` model itself. These are pure derivations from contract-definition data already on the model — no external service needed.

**Files:**

- Modify: `src/Carrier/Model/Carrier.php`
- Create: `tests/Unit/Carrier/Model/CarrierSupportTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Carrier/Model/CarrierSupportTest.php`:

```php
<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns true when carrier supports the package type', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withCapabilityPackageTypes([RefShipmentPackageTypeV2::PACKAGE, RefShipmentPackageTypeV2::MAILBOX])
        ->make();

    expect($carrier->canSupportPackageType(RefShipmentPackageTypeV2::PACKAGE))->toBeTrue()
        ->and($carrier->canSupportPackageType(RefShipmentPackageTypeV2::MAILBOX))->toBeTrue();
});

it('returns false when carrier does not support the package type', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withCapabilityPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
        ->make();

    expect($carrier->canSupportPackageType(RefShipmentPackageTypeV2::MAILBOX))->toBeFalse();
});

it('returns true when carrier supports the delivery type', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withCapabilityDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP])
        ->make();

    expect($carrier->canSupportDeliveryType(RefTypesDeliveryTypeV2::STANDARD))->toBeTrue()
        ->and($carrier->canSupportDeliveryType(RefTypesDeliveryTypeV2::PICKUP))->toBeTrue();
});

it('returns false when carrier does not support the delivery type', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withCapabilityDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD])
        ->make();

    expect($carrier->canSupportDeliveryType(RefTypesDeliveryTypeV2::EVENING))->toBeFalse();
});

it('returns true when carrier options include the shipment option key', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    expect($carrier->canSupportShipmentOption(new SignatureDefinition()))->toBeTrue();
});

it('returns false when carrier options do not include the shipment option key', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withOptions([])
        ->make();

    expect($carrier->canSupportShipmentOption(new SignatureDefinition()))->toBeFalse();
});

it('returns allowed insurance amounts as a range based on insured amount min/max', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withInsurance(0, 0, 500000)
        ->make();

    $amounts = $carrier->getAllowedInsuranceAmounts();

    expect($amounts)->toBeArray()
        ->and($amounts)->toContain(0)
        ->and($amounts)->toContain(500000);
});

it('returns an empty array when carrier does not support insurance', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withOptions([])
        ->make();

    expect($carrier->getAllowedInsuranceAmounts())->toBe([]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker compose run php composer test:unit -- --filter="CarrierSupportTest"`
Expected: FAIL — methods don't exist

- [ ] **Step 3: Add methods to Carrier model**

In `src/Carrier/Model/Carrier.php`, add imports at the top:

```php
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
```

Add these public methods to the class:

```php
/**
 * Check whether this carrier supports the given V2 package type.
 */
public function canSupportPackageType(string $v2PackageType): bool
{
    return in_array($v2PackageType, $this->packageTypes ?? [], true);
}

/**
 * Check whether this carrier supports the given V2 delivery type.
 */
public function canSupportDeliveryType(string $v2DeliveryType): bool
{
    return in_array($v2DeliveryType, $this->deliveryTypes ?? [], true);
}

/**
 * Check whether this carrier supports the given shipment option.
 */
public function canSupportShipmentOption(OrderOptionDefinitionInterface $definition): bool
{
    $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

    if (! $capabilitiesKey || ! $this->options) {
        return false;
    }

    return $this->getOptionMetadata($capabilitiesKey) !== null;
}

/**
 * Resolve the allowed insurance tier amounts from the carrier's capabilities data.
 *
 * Small ranges (≤ 50 000 cents) step in 10 000 cents; larger ranges step in 50 000 cents.
 * Returns an empty array when the carrier does not offer insurance.
 *
 * @return int[]
 */
public function getAllowedInsuranceAmounts(): array
{
    if (! $this->canSupportShipmentOption(new InsuranceDefinition())) {
        return [];
    }

    $insurance = $this->options ? $this->options->getInsurance() : null;

    if (! $insurance) {
        return [];
    }

    $insuredAmount = $insurance->getInsuredAmount();
    $min           = $insuredAmount->getMin()->getAmount();
    $max           = $insuredAmount->getMax()->getAmount();

    $step = $max - $min <= 50_000 ? 10_000 : 50_000;

    return range($min, $max, $step);
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `docker compose run php composer test:unit -- --filter="CarrierSupportTest"`
Expected: All tests PASS

- [ ] **Step 5: Run PHPStan**

Run: `docker compose run php composer analyse`
Expected: No new errors in Carrier.php

- [ ] **Step 6: Commit**

```bash
git add src/Carrier/Model/Carrier.php tests/Unit/Carrier/Model/CarrierSupportTest.php
git commit -m "$(cat <<'EOF'
feat(carrier): add canSupport* and getAllowedInsuranceAmounts methods to Carrier model

Moves boolean capability checks and insurance tier derivation from the
deprecated CarrierSchema validator onto the Carrier model. Reads directly
from stored contract-definition data — no external service needed.

Refs: INT-1504, INT-930

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 2: Update OrderOptionDefinition validate() signature

Change the interface contract from `validate(CarrierSchema $schema)` to `validate(Carrier $carrier)` and update all definitions.

**Files:**

- Modify: `src/App/Options/Contract/OrderOptionDefinitionInterface.php`
- Modify: `src/App/Options/Definition/AbstractOrderOptionDefinition.php`
- Modify: `src/App/Options/Definition/FitInMailboxDefinition.php`
- Modify: `src/App/Options/Definition/FitInDigitalStampDefinition.php`

- [ ] **Step 1: Update the interface**

In `src/App/Options/Contract/OrderOptionDefinitionInterface.php`, replace the `use` for `CarrierSchema` with `Carrier`:

```php
use MyParcelNL\Pdk\Carrier\Model\Carrier;
```

Update the `validate()` method signature:

```php
/**
 * Check whether this option is valid for the given carrier.
 */
public function validate(Carrier $carrier): bool;
```

- [ ] **Step 2: Update AbstractOrderOptionDefinition**

In `src/App/Options/Definition/AbstractOrderOptionDefinition.php`:

Remove the import:

```php
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
```

Add:

```php
use MyParcelNL\Pdk\Carrier\Model\Carrier;
```

Replace the `validate()` method body:

```php
public function validate(Carrier $carrier): bool
{
    return $carrier->canSupportShipmentOption($this);
}
```

- [ ] **Step 3: Update FitInMailboxDefinition**

In `src/App/Options/Definition/FitInMailboxDefinition.php`:

Replace the `use` statements for CarrierSchema with:

```php
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
```

Replace the `validate()` method:

```php
public function validate(Carrier $carrier): bool
{
    return $carrier->canSupportPackageType(RefShipmentPackageTypeV2::MAILBOX);
}
```

- [ ] **Step 4: Update FitInDigitalStampDefinition**

Same pattern — replace `validate()`:

```php
public function validate(Carrier $carrier): bool
{
    return $carrier->canSupportPackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP);
}
```

Add the same imports.

- [ ] **Step 5: Find any callers of validate() and update them**

Search for callers:

```bash
grep -rn "->validate(" src/ tests/ --include="*.php" | grep -iE "definition|schema|carrierSchema"
```

Update any caller that passes a `CarrierSchema` to pass the underlying `Carrier` directly. Callers that wrap a Carrier in CarrierSchema just to call `validate()` should call the method on the carrier directly.

- [ ] **Step 6: Run tests**

Run: `docker compose run php composer test:unit`
Expected: PASS

- [ ] **Step 7: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
feat(options)!: OrderOptionDefinition::validate() takes Carrier instead of CarrierSchema

AbstractOrderOptionDefinition delegates to Carrier::canSupportShipmentOption.
FitInMailboxDefinition and FitInDigitalStampDefinition use
Carrier::canSupportPackageType.

BREAKING CHANGE: OrderOptionDefinitionInterface::validate() now takes Carrier.
Platform implementations of this interface must update the signature.

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 3: Migrate InsuranceCalculator

**Files:**

- Modify: `src/App/Order/Calculator/General/InsuranceCalculator.php`

- [ ] **Step 1: Remove CarrierSchema dependency**

In `src/App/Order/Calculator/General/InsuranceCalculator.php`:

Remove:

```php
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
```

Find the `resolveAllowedInsuranceAmounts()` method. Replace its body:

```php
/**
 * Resolve the list of allowed insurance tier amounts for the given carrier.
 *
 * @return int[]
 */
private function resolveAllowedInsuranceAmounts(Carrier $carrier): array
{
    return $carrier->getAllowedInsuranceAmounts();
}
```

Remove any `CarrierSchema` usage elsewhere in the file.

- [ ] **Step 2: Run tests**

Run: `docker compose run php composer test:unit -- --filter="InsuranceCalculator"`
Expected: PASS (behavior unchanged from caller perspective)

- [ ] **Step 3: Run full suite**

Run: `docker compose run php composer test:unit`

- [ ] **Step 4: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 5: Commit**

```bash
git add src/App/Order/Calculator/General/InsuranceCalculator.php tests/
git commit -m "$(cat <<'EOF'
refactor(order): use Carrier::getAllowedInsuranceAmounts in InsuranceCalculator

Removes the remaining CarrierSchema dependency. Behavior unchanged — the
schema already read from carrier capabilities data; now the carrier model
exposes the method directly.

Refs: INT-1504, INT-930

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 4: Migrate shipment request classes

**Files:**

- Modify: `src/Shipment/Request/PostShipmentsRequest.php`
- Modify: `src/Shipment/Request/PostReturnShipmentsRequest.php`

- [ ] **Step 1: Read PostShipmentsRequest**

Read `src/Shipment/Request/PostShipmentsRequest.php` end-to-end. Find every CarrierSchema usage. The main one is multi-collo validation — `canHaveMultiCollo()` on the schema reads `$carrier->collo->getMax() > 1`. Replace with that direct check.

- [ ] **Step 2: Update PostShipmentsRequest**

Remove:

```php
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
```

Replace any `$schema->canHaveMultiCollo()` call with:

```php
$hasMultiCollo = $carrier->collo && $carrier->collo->getMax() > 1;
```

Remove the `Pdk::get(CarrierSchema::class)` / `->setCarrier(...)` boilerplate that's only present to call one method.

- [ ] **Step 3: Update PostReturnShipmentsRequest**

In `src/Shipment/Request/PostReturnShipmentsRequest.php`:

Remove the `use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;` import.

Find the `hasReturnCapabilities()` usage. `CarrierSchema::hasReturnCapabilities()` currently always returns `true` (it's a placeholder). Replace the call with a literal `true` and add a TODO comment:

```php
// @TODO: replace with a capabilities-based directionality check once the API supports inbound/outbound
$hasReturnCapabilities = true;
```

If there's no actual branch that depends on the value, simply delete the variable and the schema lookup.

- [ ] **Step 4: Run tests**

Run: `docker compose run php composer test:unit -- --filter="PostShipments|PostReturnShipments"`
Expected: PASS

- [ ] **Step 5: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 6: Commit**

```bash
git add src/Shipment/Request/
git commit -m "$(cat <<'EOF'
refactor(shipment): remove CarrierSchema from shipment request classes

Multi-collo checks read Carrier::collo directly. Return capabilities check
was always true — kept as literal with TODO for future directionality work.

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 5: Rewrite CarrierSettingsItemView

This is the biggest single-file change. The current view has partial capability-driven logic but still:

- Constructs a `CarrierSchema` wrapper
- Uses reflection to look up `CarrierSettings::ALLOW_*` / `PRICE_DELIVERY_TYPE_*` constants
- Calls `canHaveShipmentOption()`, `canHaveMondayDelivery()`, `getAllowedInsuranceAmounts()` on the schema

Goals after this task:

- No `CarrierSchema` usage at all
- No reflection-based constant lookup (uses string construction by convention instead)
- Shipment options section iterates `$this->carrier->options` and looks up matching `OrderOptionDefinition` by capabilities key
- Delivery types section iterates `$this->carrier->deliveryTypes` (V2 constants from SDK)
- Package types section (already correct) iterates `$this->carrier->packageTypes`
- `canHaveMondayDelivery` stays as a PostNL-only helper with a TODO referencing INT-1506

**Files:**

- Modify: `src/Frontend/View/CarrierSettingsItemView.php`

- [ ] **Step 1: Read the current implementation**

Read `src/Frontend/View/CarrierSettingsItemView.php` fully. Note the methods:

- Constructor sets up `$this->carrier` and `$this->carrierSchema`
- `getShipmentOptionsSettings()` — iterates `orderOptionDefinitions`, filters by `carrierSchema->canHaveShipmentOption`
- `getDeliveryTypeSettings()` — iterates `$this->carrier->deliveryTypes`, uses reflection on `CarrierSettings` constants
- `createInsuranceElement()` (or similar) — calls `$this->carrierSchema->getAllowedInsuranceAmounts()`
- Various `canHave*` checks scattered through the file
- `canHaveMondayDelivery()` check around line 515

- [ ] **Step 2: Remove CarrierSchema setup**

In the constructor, remove these lines:

```php
/** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
$schema = Pdk::get(CarrierSchema::class);
$schema->setCarrier($carrier);

$this->carrierSchema = $schema;
```

Remove the `$carrierSchema` property declaration.

Remove the import:

```php
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
```

- [ ] **Step 3: Invert getShipmentOptionsSettings() to capabilities-first**

Current approach: iterate all registered definitions, check each against carrier.

New approach: iterate the carrier's `options`, look up the matching definition by `capabilitiesOptionsKey`, build the form element from the definition.

Replace the method body:

```php
/**
 * Build shipment option form fields by iterating the carrier's capabilities options
 * and looking up the matching OrderOptionDefinition for each. Options the PDK does
 * not know about (no matching definition) are skipped.
 */
private function getShipmentOptionsSettings(): array
{
    /** @var OrderOptionDefinitionInterface[] $definitions */
    $definitions      = Pdk::get('orderOptionDefinitions');
    $definitionsByKey = $this->indexDefinitionsByCapabilitiesKey($definitions);
    $options          = $this->carrier->options;

    if (! $options) {
        return [];
    }

    $settings = [];

    foreach ($this->getPopulatedOptionKeys($options) as $capabilitiesKey) {
        $definition = $definitionsByKey[$capabilitiesKey] ?? null;

        if (! $definition) {
            continue;
        }

        $allowKey = $definition->getAllowSettingsKey();
        $priceKey = $definition->getPriceSettingsKey();

        if (! $allowKey) {
            continue;
        }

        $elements = $priceKey
            ? $this->createSettingWithPriceFields($allowKey, $priceKey)
            : [new InteractiveElement($allowKey, Components::INPUT_TOGGLE)];

        $this->makeReadOnlyWhenRequired($elements[0], $capabilitiesKey);

        $settings = array_merge($settings, $elements);
    }

    return $settings;
}

/**
 * @param  OrderOptionDefinitionInterface[] $definitions
 * @return array<string, OrderOptionDefinitionInterface>
 */
private function indexDefinitionsByCapabilitiesKey(array $definitions): array
{
    $byKey = [];

    foreach ($definitions as $definition) {
        $key = $definition->getCapabilitiesOptionsKey();

        if ($key) {
            $byKey[$key] = $definition;
        }
    }

    return $byKey;
}

/**
 * Get the camelCase capabilities keys for options the carrier has populated
 * (i.e. present in the contract-definitions response).
 *
 * @return string[]
 */
private function getPopulatedOptionKeys($options): array
{
    $populated = [];

    foreach (array_keys($options::attributeMap()) as $snakeKey) {
        $getter = 'get' . Str::studly($snakeKey);

        if (method_exists($options, $getter) && $options->{$getter}() !== null) {
            $populated[] = Str::camel($snakeKey);
        }
    }

    return $populated;
}
```

- [ ] **Step 4: Rewrite getDeliveryTypeSettings() without reflection**

Replace the reflection-based method with iteration over `$this->carrier->deliveryTypes`:

```php
/**
 * Build allow/price field pairs for each delivery type the carrier supports.
 * Pickup is handled separately in getDeliveryOptionsFields().
 */
private function getDeliveryTypeSettings(): array
{
    $settings = [];

    if (! $this->carrier->deliveryTypes) {
        return $settings;
    }

    foreach ($this->carrier->deliveryTypes as $v2DeliveryType) {
        if ($v2DeliveryType === RefTypesDeliveryTypeV2::PICKUP) {
            continue;
        }

        $pdkName  = $this->v2DeliveryTypeToPdkName($v2DeliveryType);
        $allowKey = 'allow' . Str::studly($pdkName);
        $priceKey = 'priceDeliveryType' . Str::studly($pdkName);

        $settings = array_merge(
            $settings,
            $this->createSettingWithPriceFields($allowKey, $priceKey)
        );
    }

    return $settings;
}

/**
 * Convert a V2 delivery type (e.g. STANDARD_DELIVERY) to the PDK name
 * used in settings keys (e.g. standard).
 */
private function v2DeliveryTypeToPdkName(string $v2DeliveryType): string
{
    $map = array_flip(DeliveryOptions::DELIVERY_TYPES_V2_MAP);

    return $map[$v2DeliveryType] ?? strtolower($v2DeliveryType);
}
```

- [ ] **Step 5: Replace getAllowedInsuranceAmounts call**

Find `$this->carrierSchema->getAllowedInsuranceAmounts()` and replace with:

```php
$this->carrier->getAllowedInsuranceAmounts()
```

- [ ] **Step 6: Replace canHave\* calls with Carrier model methods**

Find every `$this->carrierSchema->canHaveShipmentOption(ClassName::class)` and replace:

```php
$this->carrier->canSupportShipmentOption(new ClassName())
```

- [ ] **Step 7: Handle canHaveMondayDelivery as explicit exception**

Find `$this->carrierSchema->canHaveMondayDelivery()`. Replace with a local helper on the view:

```php
/**
 * Monday delivery is a delivery-options widget parameter, not a carrier capability.
 * Currently only PostNL supports it. See INT-1506 for discussion on whether this
 * should move to a merchant-level checkout setting or be removed.
 */
private function canHaveMondayDelivery(): bool
{
    return $this->carrier->carrier === RefCapabilitiesSharedCarrierV2::POSTNL;
}
```

Add the import if missing:

```php
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
```

Replace caller with `$this->canHaveMondayDelivery()`.

- [ ] **Step 8: Run tests**

Run: `docker compose run php composer test:unit -- --filter="CarrierSettingsItemView|AdminContext"`

Update snapshots if output changed intentionally:

```bash
yarn test:unit:snapshot
```

Review snapshot diffs carefully — the output should reflect the SAME capability-driven behavior through a cleaner code path. If fields appear or disappear for a carrier that shouldn't be affected, something is wrong.

- [ ] **Step 9: Run full suite**

Run: `docker compose run php composer test:unit`

- [ ] **Step 10: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 11: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
feat(admin): render carrier settings from stored capabilities, no reflection or CarrierSchema

Shipment options section iterates the carrier's options and looks up matching
OrderOptionDefinition by capabilitiesOptionsKey. Delivery types section
iterates carrier.deliveryTypes and derives settings keys by convention.
Package types section already iterated carrier.packageTypes.

canHaveMondayDelivery is kept as a local PostNL-only exception with a TODO
referencing INT-1506 (needs product decision — widget config vs carrier capability).

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 6: Remove CarrierSchema class

After all consumers have migrated, verify nothing references CarrierSchema anymore and delete it.

**Files:**

- Delete: `src/Validation/Validator/CarrierSchema.php`
- Delete: any test file specifically for CarrierSchema

- [ ] **Step 1: Verify no remaining references**

```bash
grep -rn "CarrierSchema" src/ tests/ --include="*.php"
```

Expected: No results (or only references inside the file being deleted). If anything else appears, go back and fix that file first.

- [ ] **Step 2: Delete the class**

```bash
rm src/Validation/Validator/CarrierSchema.php
```

- [ ] **Step 3: Delete CarrierSchema-specific tests**

```bash
find tests -name "*CarrierSchema*" -type f -delete
```

- [ ] **Step 4: Run full suite**

Run: `docker compose run php composer test:unit`
Expected: PASS

- [ ] **Step 5: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
chore!: remove deprecated CarrierSchema validator

All consumers have been migrated to Carrier model methods.

BREAKING CHANGE: MyParcelNL\Pdk\Validation\Validator\CarrierSchema removed.
Platforms using this class directly should use Carrier::canSupport* methods
or Carrier::getAllowedInsuranceAmounts instead.

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 7: Remove deprecated CarrierSettings constants

**Files:**

- Modify: `src/Settings/Model/CarrierSettings.php`

- [ ] **Step 1: Audit remaining usages**

```bash
grep -rn "CarrierSettings::ALLOW_ONLY_RECIPIENT\|CarrierSettings::ALLOW_PRIORITY_DELIVERY\|CarrierSettings::ALLOW_SAME_DAY_DELIVERY\|CarrierSettings::ALLOW_SATURDAY_DELIVERY\|CarrierSettings::ALLOW_SIGNATURE\|CarrierSettings::EXPORT_AGE_CHECK\|CarrierSettings::EXPORT_HIDE_SENDER\|CarrierSettings::EXPORT_INSURANCE[^_]\|CarrierSettings::EXPORT_LARGE_FORMAT\|CarrierSettings::EXPORT_ONLY_RECIPIENT\|CarrierSettings::EXPORT_RECEIPT_CODE\|CarrierSettings::EXPORT_RETURN[^_]\|CarrierSettings::EXPORT_SIGNATURE\|CarrierSettings::EXPORT_TRACKED\|CarrierSettings::EXPORT_COLLECT\|CarrierSettings::EXPORT_FRESH_FOOD\|CarrierSettings::EXPORT_FROZEN\|CarrierSettings::EXPORT_PRIORITY_DELIVERY\|CarrierSettings::PRICE_ONLY_RECIPIENT\|CarrierSettings::PRICE_SIGNATURE\|CarrierSettings::PRICE_PRIORITY_DELIVERY\|CarrierSettings::PRICE_COLLECT" src/ tests/ --include="*.php"
```

For each caller, replace with the appropriate definition getter:

- `CarrierSettings::ALLOW_ONLY_RECIPIENT` → `(new OnlyRecipientDefinition())->getAllowSettingsKey()`
- `CarrierSettings::EXPORT_AGE_CHECK` → `(new AgeCheckDefinition())->getCarrierSettingsKey()`
- `CarrierSettings::PRICE_SIGNATURE` → `(new SignatureDefinition())->getPriceSettingsKey()`

…and so on for each. The `@deprecated` tag on each constant comment identifies the replacement definition class.

- [ ] **Step 2: Remove the deprecated constants**

In `src/Settings/Model/CarrierSettings.php`, remove the constant declarations marked `@deprecated`. Also remove their entries from `$attributes` and `$casts` — these are already populated dynamically by `initializeResolvesOptionAttributes()`.

- [ ] **Step 3: Run tests**

Run: `docker compose run php composer test:unit`

- [ ] **Step 4: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
chore!: remove deprecated CarrierSettings constants

ALLOW_*, EXPORT_*, PRICE_* constants marked @deprecated and replaced by
OrderOptionDefinition getters have been removed. Attributes are still
populated dynamically via initializeResolvesOptionAttributes.

BREAKING CHANGE: Direct references to these constants must use
corresponding OrderOptionDefinition::getAllowSettingsKey() /
getCarrierSettingsKey() / getPriceSettingsKey() calls.

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 8: Remove deprecated ShipmentOptions constants

**Files:**

- Modify: `src/Shipment/Model/ShipmentOptions.php`

- [ ] **Step 1: Audit usages**

```bash
grep -rn "ShipmentOptions::INSURANCE\|ShipmentOptions::AGE_CHECK\|ShipmentOptions::DIRECT_RETURN\|ShipmentOptions::HIDE_SENDER\|ShipmentOptions::LARGE_FORMAT\|ShipmentOptions::ONLY_RECIPIENT\|ShipmentOptions::PRIORITY_DELIVERY\|ShipmentOptions::RECEIPT_CODE\|ShipmentOptions::SAME_DAY_DELIVERY\|ShipmentOptions::SATURDAY_DELIVERY\|ShipmentOptions::MONDAY_DELIVERY\|ShipmentOptions::SIGNATURE\|ShipmentOptions::TRACKED\|ShipmentOptions::COLLECT\|ShipmentOptions::EXCLUDE_PARCEL_LOCKERS\|ShipmentOptions::FRESH_FOOD\|ShipmentOptions::FROZEN\|ShipmentOptions::COOLED_DELIVERY\|ShipmentOptions::ALL_SHIPMENT_OPTIONS" src/ tests/ --include="*.php"
```

Replace callers with `OrderOptionDefinition::getShipmentOptionsKey()` or `ShipmentOptions::getAllShipmentOptionKeys()`:

- `ShipmentOptions::SIGNATURE` → `(new SignatureDefinition())->getShipmentOptionsKey()`
- `ShipmentOptions::ALL_SHIPMENT_OPTIONS` → `ShipmentOptions::getAllShipmentOptionKeys()`

- [ ] **Step 2: Remove the deprecated constants and array**

Remove all `@deprecated` constants in `src/Shipment/Model/ShipmentOptions.php`. Remove the `ALL_SHIPMENT_OPTIONS` array. Keep `LABEL_DESCRIPTION` (not deprecated).

- [ ] **Step 3: Run tests**

Run: `docker compose run php composer test:unit`

- [ ] **Step 4: Run PHPStan**

Run: `docker compose run php composer analyse`

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
chore!: remove deprecated ShipmentOptions constants

Constants marked @deprecated replaced by OrderOptionDefinition::getShipmentOptionsKey().
ALL_SHIPMENT_OPTIONS array replaced by ShipmentOptions::getAllShipmentOptionKeys().

BREAKING CHANGE: Direct references to deprecated constants must use
OrderOptionDefinition getters. ALL_SHIPMENT_OPTIONS consumers should call
getAllShipmentOptionKeys() instead.

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

### Task 9: Dead-code, deprecated-config, and `@TODO` sweep

**Goal:** Final cleanup pass across the PDK repo. Capabilities data now drives carrier behaviour end-to-end through Plans 1–3. Anything that used to provide the same information from hardcoded PHP, JSON schemas, or stale config is dead. All `@TODO` comments that reference capabilities must be resolved here — **there will be no follow-up cleanup plan**. A remaining `@TODO` means either it gets actioned now, or a dedicated Jira ticket is opened to track it.

This task runs **after** Tasks 1–8 of this plan are complete and green. Because this is step 4 of 4 in the INT-930 chain, the sweep covers leftovers from every prior plan in the series — including the admin-capabilities-context work (step 3).

#### Scope — what to hunt for

Treat each of the below as a candidate for removal. Only remove after `grep` confirms there are no live consumers (including tests, snapshots, config, plugin extension points, and branches in adjacent repos).

- **Residual JSON schemas** for carriers / package types / delivery types / shipment options in `src/`, `resources/`, `schemas/`, `validation/schemas/`.
- **Hardcoded carrier / option maps** (`const ALLOWED_*`, `const CARRIER_*_MAP`, `array_fill_keys(...)` of carrier or option names used as validation scaffolding). **Distinguish from V1-API ID maps** — `PACKAGE_TYPES_NAMES_IDS_MAP`, `DELIVERY_TYPES_NAMES_IDS_MAP`, `*_V2_MAP`, `DELIVERY_TYPE_*_ID`, `PACKAGE_TYPE_*_ID`, `getDeliveryTypeId()`, `getPackageTypeId()` — those **stay**, per this plan's Existing-work inventory.
- **Dead `Validator/` classes** under `src/Validation/Validator/` — neighbours of `CarrierSchema` that do the same kind of static wrapping.
- **`@deprecated` markers in affected areas** (`src/Carrier/`, `src/Context/`, `src/App/Options/`, `src/App/Order/`, `src/Settings/`) whose replacements are now fully wired.
- **Dead DI bindings** in `config/pdk-services.php`, `config/actions.php`, any `config/*.php` — `autowire(...)` / `bind` / `singleton` entries pointing at removed classes.
- **Snapshot tests** in `tests/__snapshots__/` asserting the shape of since-removed classes.
- **Thin capability-wrapping helpers** — classes whose body boils down to "call the SDK getter on `$carrier->options` and return a scalar". This plan's additions to `Carrier` (`canSupportShipmentOption` et al., plus the pre-existing `getOptionMetadata`) replace these.

#### Scope — what NOT to touch

- **V1 API paths** — `PostShipmentsRequest`, `PostReturnShipmentsRequest`, Fulfilment. The ID maps / `getDeliveryTypeId()` / `getPackageTypeId()` listed above stay.
- **`delivery-options` / checkout widget code** — covered by Plan 1 (checkout-capabilities-api).
- **`CarrierSettingsItemView`** beyond what Task 5 of this plan already did.

#### `@TODO` hunt — no follow-ups allowed

- [ ] **Step 1: Enumerate capabilities-related TODOs**

```bash
grep -rn "@TODO" src/ tests/ config/ --include='*.php' | grep -iE "capab|carrier|option|schema|contract|isRequired|isSelectedByDefault|excludes|requires|delivery type|package type"
```

Open each hit. For each:

- If it's **actionable within this plan's scope and fixable in under ~30 lines**, fix it now as part of this task. Include in the relevant category commit below.
- If it's **actionable but larger** (cross-cutting refactor, spec decision, etc.), create a Jira sub-task under INT-930 with the TODO text + file:line reference, and replace the `@TODO` in the code with a reference to the ticket (`@TODO INT-XXXX: <one-line context>`). Then the TODO is no longer dangling — it's tracked.
- If it's **no longer relevant** (the concern has been addressed by earlier plans), delete the TODO comment.

Do NOT leave any untagged `@TODO` that mentions capabilities / carriers / options. The default bar is: a reader a year from now must be able to trace every such TODO to either live code or a tracked ticket.

- [ ] **Step 2: Commit the TODO resolutions**

One commit for all TODO resolutions, grouped by file:

```
chore(cleanup): resolve capabilities-related @TODOs

Actioned: <list short descriptions>
Retagged with tickets: <list file:line → ticket>
Removed as obsolete: <list file:line>

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
```

#### Dead-code inventory and removal

- [ ] **Step 3: Inventory candidate JSON schemas**

```bash
find src resources schemas -name '*.json' -type f 2>/dev/null
find config -name '*.json' -type f 2>/dev/null
```

For each hit, `grep -rn "<basename>"` across the repo + `~/projects/js-pdk/`, `~/projects/delivery-options/`, and the docker-wordpress / docker-prestashop plugins. If no live consumer, queue for removal.

- [ ] **Step 4: Inventory static carrier/option arrays**

```bash
grep -rn "const ALLOWED_\|const CARRIERS_\|const PACKAGE_TYPES\|const DELIVERY_TYPES\|const SHIPMENT_OPTIONS" src/ | grep -v "_ID\|_IDS_MAP\|_V2_MAP"
```

The grep filters V1 ID constants. For each remaining hit, check where it's read. If only by code removed in Plans 1–3 or by code that can now use capabilities, queue for removal.

- [ ] **Step 5: Inventory validator/schema wrappers**

```bash
ls src/Validation/Validator/
grep -rn "class.*Schema\b" src/ --include='*.php'
```

- [ ] **Step 6: Inventory `@deprecated` in affected areas**

```bash
grep -rn "@deprecated" src/Carrier/ src/Context/ src/App/Options/ src/App/Order/ src/Settings/ --include='*.php'
```

- [ ] **Step 7: Inventory dead DI bindings**

```bash
grep -rn "autowire\|bind\|singleton" config/ --include='*.php'
```

For each binding, confirm the target class exists.

- [ ] **Step 8: Produce a removal proposal**

Compile a list in your scratch notes (not committed): file path + what it is + why dead + which consumers you verified are gone. Walk it with a **critical eye**: plugin extension points, test fixtures, adjacent repos. When in doubt, leave it — don't create a second cleanup pass.

- [ ] **Step 9: Remove and commit one category at a time**

Categories: schemas, validator wrappers, constants, config bindings, deprecated methods. One commit per category makes review + rollback granular.

After every removal:

```bash
docker compose run php composer test:unit
docker compose run php composer analyse
```

Both must pass. If something breaks, restore it — the thing wasn't dead.

Commit format:

```
chore(cleanup): remove <category> superseded by capabilities data

<one sentence on what was removed and what replaces it>

Refs: INT-1504

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
```

- [ ] **Step 10: If nothing is removable**

That's a valid outcome. Note it in the PR description under "Cleanup sweep: no removable dead code found — earlier plans already caught it." Move on.

#### Escalation

- If a candidate looks dead in the PDK but is referenced by a plugin, js-pdk, or delivery-options, **don't remove**. Retag the TODO (or create a follow-up ticket) per Step 1, and continue.
- If removing a class requires editing more than ~5 files outside this plan's modified scope, STOP and flag it. The cleanup expanded beyond this plan's charter.

---

### Task 10: Multi-PHP verification and PR

- [ ] **Step 1: Run on PHP 7.4**

```bash
PHP_VERSION=7.4 docker compose run php composer update --no-interaction --no-progress
PHP_VERSION=7.4 docker compose run php composer test:unit
```

Expected: All tests PASS

- [ ] **Step 2: Run on PHP 8.1+**

```bash
PHP_VERSION=8.1 docker compose run php composer update --no-interaction --no-progress
PHP_VERSION=8.1 docker compose run php composer test:unit
```

Expected: All tests PASS

- [ ] **Step 3: Run PHPStan on latest PHP**

```bash
docker compose run php composer analyse
```

Verify no new errors introduced compared to the baseline before this plan.

- [ ] **Step 4: Push branch and create PR**

```bash
git push -u origin feat/INT-1504-carrier-schema-cleanup
```

Create PR against `v4-capabilities` using `gh pr create`:

Title: `feat(admin)!: remove CarrierSchema and deprecated carrier config surface`

Body:

```
Completes the INT-930 migration to the capabilities API by removing the
deprecated CarrierSchema validator, removing deprecated constants on
CarrierSettings and ShipmentOptions, and rewriting the admin carrier
settings view to work directly from stored carrier contract-definition
data and registered OrderOptionDefinition classes.

## Depends on

- #449 (INT-1500: checkout capabilities)
- #450 (INT-1501: order calculator capabilities)

## Changes

- `Carrier` model gains `canSupportPackageType`, `canSupportDeliveryType`,
  `canSupportShipmentOption`, `getAllowedInsuranceAmounts` methods.
- `OrderOptionDefinitionInterface::validate()` takes `Carrier` instead of
  `CarrierSchema`.
- `CarrierSettingsItemView` iterates the carrier's own `options`,
  `deliveryTypes`, and `packageTypes`. No reflection, no CarrierSchema.
- `CarrierSchema` class deleted.
- Deprecated `CarrierSettings` (`ALLOW_*`, `EXPORT_*`, `PRICE_*`) and
  `ShipmentOptions` constants removed — attributes still populated
  dynamically from registered definitions.
- `canHaveMondayDelivery` retained as a PostNL-only exception (see INT-1506
  for future refinement).
- V1 ID constants preserved — still used by V1 shipment/return/fulfilment APIs.

## Test plan

- [ ] Admin carrier settings page renders for each carrier
- [ ] Required options (isRequired) shown as read-only
- [ ] Package type / delivery type field lists reflect carrier capabilities
- [ ] Monday delivery toggle shown only for PostNL
- [ ] Insurance tiers correct on order export
- [ ] V1 shipment creation still works
- [ ] V1 return shipment creation still works
- [ ] Fulfilment order export still works

BREAKING CHANGE: CarrierSchema class removed. OrderOptionDefinitionInterface::validate()
now takes Carrier. Deprecated CarrierSettings (ALLOW_*, EXPORT_*, PRICE_*)
and ShipmentOptions constants removed. Callers must use Carrier::canSupport*
methods or OrderOptionDefinition getters.

Refs: INT-1504, INT-930
```

---

## Summary of changes per file

| File                                                           | Change                                                           |
| -------------------------------------------------------------- | ---------------------------------------------------------------- |
| `src/Carrier/Model/Carrier.php`                                | Add `canSupport*` methods + `getAllowedInsuranceAmounts`         |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`  | `validate()` takes `Carrier`                                     |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php` | Delegate to `Carrier::canSupportShipmentOption`                  |
| `src/App/Options/Definition/FitInMailboxDefinition.php`        | Use `Carrier::canSupportPackageType`                             |
| `src/App/Options/Definition/FitInDigitalStampDefinition.php`   | Use `Carrier::canSupportPackageType`                             |
| `src/App/Order/Calculator/General/InsuranceCalculator.php`     | Use `$carrier->getAllowedInsuranceAmounts()`                     |
| `src/Shipment/Request/PostShipmentsRequest.php`                | Use `$carrier->collo` directly                                   |
| `src/Shipment/Request/PostReturnShipmentsRequest.php`          | Remove CarrierSchema, literal `true` + TODO                      |
| `src/Frontend/View/CarrierSettingsItemView.php`                | Capabilities-first iteration, no reflection, local Monday helper |
| `src/Settings/Model/CarrierSettings.php`                       | Remove deprecated constants + attribute/cast entries             |
| `src/Shipment/Model/ShipmentOptions.php`                       | Remove deprecated constants + `ALL_SHIPMENT_OPTIONS`             |
| `src/Validation/Validator/CarrierSchema.php`                   | **Deleted**                                                      |
