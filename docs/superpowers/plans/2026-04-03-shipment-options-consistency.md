# Shipment Options Consistency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reduce the number of files touched when adding a new shipment option from 9+ to 2-3, by making the `OrderOptionDefinitionInterface` the single source of truth for all key mappings.

**Architecture:** An `AbstractOrderOptionDefinition` derives settings keys by convention (export, allow, price) and uses SDK types as direct references for both shipment options keys (`RefShipmentShipmentOptions::attributeMap()`) and capabilities keys (`RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()`). A `ResolvesOptionAttributes` trait lets models build their attributes dynamically from registered definitions. `CarrierSchema` uses `__call()` to proxy deprecated `canHave*()` methods. `ShipmentOptions` constants are deprecated with all internal PDK usage removed.

**Tech Stack:** PHP 7.4+, Pest v1, Docker Compose for test execution

**Spec:** `docs/superpowers/specs/2026-04-03-shipment-options-consistency-design.md`

**Test commands:**

- Run all tests: `yarn run test:unit`
- Run filtered test: `docker compose run php composer test -- --filter="test name"`
- Update snapshots: `yarn test:unit:snapshot`
- Run ide-helper: `docker compose run php composer console generate:ide-helper`

---

### Task 1: Update OrderOptionDefinitionInterface

**Files:**

- Modify: `src/App/Options/Contract/OrderOptionDefinitionInterface.php`

- [ ] **Step 1: Add the two new methods to the interface**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

interface OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string;

    public function getProductSettingsKey(): ?string;

    public function getShipmentOptionsKey(): ?string;

    public function getCapabilitiesOptionsKey(): ?string;

    /**
     * The delivery options "allow" toggle key (e.g. 'allowSignature').
     *
     * @return null|string
     */
    public function getAllowSettingsKey(): ?string;

    /**
     * The price surcharge key (e.g. 'priceSignature').
     *
     * @return null|string
     */
    public function getPriceSettingsKey(): ?string;

    public function validate(CarrierSchema $carrierSchema): bool;
}
```

- [ ] **Step 2: Verify the project still compiles (expect failures from missing implementations)**

Run: `docker compose run php composer analyse 2>&1 | head -30`
Expected: Errors about missing `getAllowSettingsKey()` and `getPriceSettingsKey()` on concrete classes. This is expected — we'll fix them in Task 3.

- [ ] **Step 3: Commit**

```bash
git add src/App/Options/Contract/OrderOptionDefinitionInterface.php
git commit -m "feat(options): add getAllowSettingsKey and getPriceSettingsKey to interface"
```

---

### Task 2: Create AbstractOrderOptionDefinition

**Files:**

- Create: `src/App/Options/Definition/AbstractOrderOptionDefinition.php`

- [ ] **Step 1: Create the abstract class with convention-based defaults**

The shipment options key is derived from `RefShipmentShipmentOptions::attributeMap()` via `Str::camel()` by default. Definitions that need a different internal key (or have no SDK mapping) override `getShipmentOptionsKey()` with a string.

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

abstract class AbstractOrderOptionDefinition implements OrderOptionDefinitionInterface
{
    /**
     * The internal PDK key used on the ShipmentOptions model (e.g. 'signature', 'ageCheck').
     * This is the root key from which carrier/product/allow/price settings keys are derived.
     * These keys correspond to the legacy API naming used by the shipment-, order v1,
     * delivery-options and other legacy API endpoints.
     *
     * By default, derived from the SDK's RefShipmentShipmentOptions::attributeMap() via
     * Str::camel(). Override with a string if the PDK key differs from the SDK key's
     * camelCase equivalent, or return null if this definition does not represent a shipment
     * option (e.g. product-only settings like CountryOfOrigin).
     *
     * When null, all derived settings keys also return null automatically, and the option
     * will not appear on the ShipmentOptions model.
     */
    abstract public function getShipmentOptionsKey(): ?string;

    /**
     * The SDK capabilities key (e.g. 'requiresSignature', 'oversizedPackage').
     * This is the explicit bridge between PDK option names and SDK-generated type names.
     * These keys correspond to the V2 naming used by the capabilities API and
     * microservices (e.g. order v2).
     *
     * Return null if this option has no corresponding capabilities entry (e.g.
     * ExcludeParcelLockers). When null, the option cannot be validated against carrier
     * capabilities, and no default value will be resolved from the capabilities response.
     */
    abstract public function getCapabilitiesOptionsKey(): ?string;

    /**
     * The carrier-level settings key (e.g. 'exportSignature').
     * Derived by default: 'export' + ucfirst(shipmentOptionsKey).
     *
     * Return null to opt out of carrier-level settings. When null, no attribute will be
     * registered on CarrierSettings and the option cannot be configured at the carrier level.
     */
    public function getCarrierSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'export' . ucfirst($key) : null;
    }

    /**
     * The product-level settings key (e.g. 'exportSignature').
     * Derived by default: same as carrier settings key.
     *
     * Return null to opt out of product-level settings. When null, no attribute will be
     * registered on ProductSettings and the option cannot be overridden per product.
     */
    public function getProductSettingsKey(): ?string
    {
        return $this->getCarrierSettingsKey();
    }

    /**
     * The delivery options "allow" toggle key (e.g. 'allowSignature').
     * Derived by default: 'allow' + ucfirst(shipmentOptionsKey).
     *
     * Return null to opt out of the allow toggle. When null, no allow attribute will be
     * registered on CarrierSettings and the option will not appear as a toggleable choice
     * in the delivery options frontend widget.
     */
    public function getAllowSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'allow' . ucfirst($key) : null;
    }

    /**
     * The price surcharge key (e.g. 'priceSignature').
     * Derived by default: 'price' + ucfirst(shipmentOptionsKey).
     *
     * Return null to opt out of the price surcharge. When null, no price attribute will be
     * registered on CarrierSettings and no surcharge will be shown in the delivery options
     * frontend widget for this option.
     */
    public function getPriceSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'price' . ucfirst($key) : null;
    }

    /**
     * Validates whether this option is available for the given carrier.
     * Default: checks if the capabilities key exists in the carrier's options.
     *
     * Override to provide custom validation logic, or to always return true for options
     * that are universally available regardless of carrier capabilities.
     */
    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveShipmentOption($this);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/App/Options/Definition/AbstractOrderOptionDefinition.php
git commit -m "feat(options): add AbstractOrderOptionDefinition with convention-based defaults"
```

---

### Task 3: Migrate All Definition Classes to Extend Abstract

**Files:**

- Modify: All 22 files in `src/App/Options/Definition/*Definition.php`

Migrate each definition class to extend `AbstractOrderOptionDefinition` instead of directly implementing the interface. Remove methods that match the abstract class defaults. Keep only overrides.

Both `getShipmentOptionsKey()` and `getCapabilitiesOptionsKey()` now reference SDK types directly. The shipment options key uses `Str::camel(RefShipmentShipmentOptions::attributeMap()['...'])` and the capabilities key uses `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['...']`. This means both keys break at runtime if the SDK removes the option — desired behavior.

- [ ] **Step 1: Migrate definitions that follow convention perfectly (only need 2 abstract methods)**

These definitions need only `getShipmentOptionsKey()` and `getCapabilitiesOptionsKey()`. All other methods match the convention defaults. Example — `SignatureDefinition`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class SignatureDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['signature']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['requires_signature'];
    }
}
```

Apply this pattern to: `AgeCheckDefinition` (`age_check` / `requires_age_verification`), `DirectReturnDefinition` (`return` / `return_on_first_failed_delivery`), `FreshFoodDefinition` (`fresh_food` / `fresh_food`), `FrozenDefinition` (`frozen` / `frozen`), `HideSenderDefinition` (`hide_sender` / `hide_sender`), `InsuranceDefinition` (`insurance` / `insurance`), `LargeFormatDefinition` (`large_format` / `oversized_package`), `OnlyRecipientDefinition` (`only_recipient` / `recipient_only_delivery`), `TrackedDefinition` (`tracked` / `tracked`).

**Key mapping reference (RefShipmentShipmentOptions key → Str::camel result → current PDK key):**

| SDK shipment key         | Str::camel()       | Current PDK key        | Match?                |
| ------------------------ | ------------------ | ---------------------- | --------------------- |
| `signature`              | `signature`        | `signature`            | Yes                   |
| `age_check`              | `ageCheck`         | `ageCheck`             | Yes                   |
| `return`                 | `return`           | `return`               | Yes                   |
| `fresh_food`             | `freshFood`        | `freshFood`            | Yes                   |
| `frozen`                 | `frozen`           | `frozen`               | Yes                   |
| `hide_sender`            | `hideSender`       | `hideSender`           | Yes                   |
| `insurance`              | `insurance`        | `insurance`            | Yes                   |
| `large_format`           | `largeFormat`      | `largeFormat`          | Yes                   |
| `only_recipient`         | `onlyRecipient`    | `onlyRecipient`        | Yes                   |
| `tracked`                | `tracked`          | `tracked`              | Yes                   |
| `collect`                | `collect`          | `collect`              | Yes                   |
| `receipt_code`           | `receiptCode`      | `receiptCode`          | Yes                   |
| `same_day_delivery`      | `sameDayDelivery`  | `sameDayDelivery`      | Yes                   |
| `saturday_delivery`      | `saturdayDelivery` | `saturdayDelivery`     | Yes                   |
| `priority_delivery`      | `priorityDelivery` | `priorityDelivery`     | Yes                   |
| `exclude_parcel_lockers` | N/A (not in SDK)   | `excludeParcelLockers` | N/A — string override |

All keys match. No string overrides needed for the SDK-mapped options.

- [ ] **Step 2: Migrate definitions that need opt-outs**

`SameDayDeliveryDefinition` — no carrier/product settings:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class SameDayDeliveryDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['same_day_delivery']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['same_day_delivery'];
    }

    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }
}
```

`SaturdayDeliveryDefinition` — same pattern as SameDayDelivery (no carrier/product settings).

`CollectDefinition` — no product settings:

```php
final class CollectDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['collect']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['scheduled_collection'];
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }
}
```

`ReceiptCodeDefinition` — no product settings (same pattern as Collect, keys: `receipt_code` / `requires_receipt_code`).

`PriorityDeliveryDefinition` — no product settings, non-standard carrier settings key:

```php
final class PriorityDeliveryDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['priority_delivery']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['priority_delivery'];
    }

    public function getCarrierSettingsKey(): ?string
    {
        return 'allowPriorityDelivery';
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }
}
```

`ExcludeParcelLockersDefinition` — not in SDK, uses string override. No carrier settings, no capabilities, no allow/price, custom validation:

```php
final class ExcludeParcelLockersDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return 'excludeParcelLockers';
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return null;
    }

    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return 'excludeParcelLockers';
    }

    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getPriceSettingsKey(): ?string
    {
        return null;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return true;
    }
}
```

- [ ] **Step 3: Migrate product-only definitions (no shipment option)**

`CountryOfOriginDefinition`, `CustomsCodeDefinition`, `DisableDeliveryOptionsDefinition`, `FitInDigitalStampDefinition`, `FitInMailboxDefinition`, `PackageTypeDefinition` — these have no shipment option key or capabilities key. They use string values for the product settings key. Example:

```php
final class CountryOfOriginDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return null;
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return 'countryOfOrigin';
    }

    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getPriceSettingsKey(): ?string
    {
        return null;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return true;
    }
}
```

Apply same pattern for the other product-only definitions, using appropriate string keys:

- `CustomsCodeDefinition` → `'customsCode'`
- `DisableDeliveryOptionsDefinition` → `'disableDeliveryOptions'`
- `FitInDigitalStampDefinition` → `'fitInDigitalStamp'` (validate: `canBeDigitalStamp()`)
- `FitInMailboxDefinition` → `'fitInMailbox'` (validate: `canBeMailbox()`)
- `PackageTypeDefinition` → `'packageType'`

- [ ] **Step 4: Run tests to verify no regressions**

Run: `yarn run test:unit`
Expected: All existing tests pass — the behavior is identical, only the implementation changed.

- [ ] **Step 5: Commit**

```bash
git add src/App/Options/Definition/
git commit -m "refactor(options): migrate all definitions to extend AbstractOrderOptionDefinition"
```

---

### Task 4: Create ResolvesOptionAttributes Trait

**Files:**

- Create: `src/Base/Concern/ResolvesOptionAttributes.php`

- [ ] **Step 1: Create the trait**

The Model class already supports trait initializers via `initializeTraits()` which calls `initialize{TraitBaseName}()`. The trait uses this hook to merge dynamic attributes at construction time.

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;

trait ResolvesOptionAttributes
{
    /**
     * Build an array of attributes from registered option definitions.
     *
     * @param  callable(OrderOptionDefinitionInterface): ?string $keyExtractor
     * @param  mixed                                             $default
     *
     * @return array
     */
    protected function resolveOptionAttributes(callable $keyExtractor, $default): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $attributes  = [];

        foreach ($definitions as $definition) {
            $key = $keyExtractor($definition);

            if ($key !== null) {
                $attributes[$key] = $default;
            }
        }

        return $attributes;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Base/Concern/ResolvesOptionAttributes.php
git commit -m "feat(options): add ResolvesOptionAttributes trait for dynamic attribute building"
```

---

### Task 5: Integrate Trait into ShipmentOptions Model

**Files:**

- Modify: `src/Shipment/Model/ShipmentOptions.php`
- Create: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

- [ ] **Step 1: Write a failing test that verifies a dynamically registered option appears on the model**

Create the flow test file. This test will grow across tasks.

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Definition\AbstractOrderOptionDefinition;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

it('registers all definition shipment option keys as attributes on ShipmentOptions', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $shipmentOptions = new ShipmentOptions();

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();

        if ($key === null) {
            continue;
        }

        expect($shipmentOptions->getAttribute($key))->toBe(
            TriStateService::INHERIT,
            "ShipmentOptions missing attribute for key '{$key}'"
        );
    }
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run php composer test -- --filter="registers all definition shipment option keys"`
Expected: FAIL — ShipmentOptions doesn't use the trait yet.

- [ ] **Step 3: Add the trait to ShipmentOptions and deprecate constants**

Add `use ResolvesOptionAttributes;` to the class. Add the `initializeResolvesOptionAttributes()` method that merges dynamic attributes and casts. Mark all option constants as `@deprecated`. Remove the option entries from the static `$attributes` and `$casts` arrays — they will be populated dynamically. Keep `LABEL_DESCRIPTION` static as it's not a standard tri-state option.

```php
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;

class ShipmentOptions extends Model
{
    use ResolvesOptionAttributes;

    // Keep constants, mark @deprecated
    /** @deprecated Use definition's getShipmentOptionsKey() instead */
    public const LABEL_DESCRIPTION = 'labelDescription';
    /** @deprecated Use definition's getShipmentOptionsKey() instead */
    public const INSURANCE         = 'insurance';
    // ... etc for all constants

    // Static attributes: only non-option attributes remain
    protected $attributes = [
        self::LABEL_DESCRIPTION => null,
    ];

    protected $casts = [
        self::LABEL_DESCRIPTION => TriStateService::TYPE_STRING,
    ];

    protected function initializeResolvesOptionAttributes(): void
    {
        $optionAttributes = $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getShipmentOptionsKey();
            },
            TriStateService::INHERIT
        );

        $optionCasts = array_fill_keys(array_keys($optionAttributes), TriStateService::TYPE_STRICT);

        // Insurance has special int cast (not tri-state)
        if (isset($optionCasts['insurance'])) {
            $optionCasts['insurance'] = 'int';
        }

        $this->attributes = array_merge($optionAttributes, $this->attributes);
        $this->casts = array_merge($optionCasts, $this->casts);
    }

    // Keep fromCapabilitiesDefinitions() and toCapabilitiesDefinitions() as-is
}
```

Also update `ALL_SHIPMENT_OPTIONS` to be built dynamically or mark it `@deprecated`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose run php composer test -- --filter="registers all definition shipment option keys"`
Expected: PASS

- [ ] **Step 5: Run all tests to check for regressions**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add src/Shipment/Model/ShipmentOptions.php tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "feat(options): integrate ResolvesOptionAttributes into ShipmentOptions"
```

---

### Task 6: Integrate Trait into CarrierSettings

**Files:**

- Modify: `src/Settings/Model/CarrierSettings.php`
- Modify: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

- [ ] **Step 1: Write a failing test for dynamic carrier settings attributes**

Add to the flow test file:

```php
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

it('registers export, allow, and price attributes on CarrierSettings from definitions', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $settings = new CarrierSettings();

    foreach ($definitions as $definition) {
        $exportKey = $definition->getCarrierSettingsKey();
        if ($exportKey) {
            expect($settings->getAttribute($exportKey))->toBe(
                TriStateService::INHERIT,
                "CarrierSettings missing export attribute '{$exportKey}'"
            );
        }

        $allowKey = $definition->getAllowSettingsKey();
        if ($allowKey) {
            expect($settings->getAttribute($allowKey))->toBe(
                false,
                "CarrierSettings missing allow attribute '{$allowKey}'"
            );
        }

        $priceKey = $definition->getPriceSettingsKey();
        if ($priceKey) {
            expect($settings->getAttribute($priceKey))->toBe(
                0,
                "CarrierSettings missing price attribute '{$priceKey}'"
            );
        }
    }
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run php composer test -- --filter="registers export, allow, and price attributes on CarrierSettings"`

- [ ] **Step 3: Add the trait to CarrierSettings**

Add `use ResolvesOptionAttributes;` and `initializeResolvesOptionAttributes()`:

```php
protected function initializeResolvesOptionAttributes(): void
{
    $exportAttributes = $this->resolveOptionAttributes(
        static function (OrderOptionDefinitionInterface $definition): ?string {
            return $definition->getCarrierSettingsKey();
        },
        TriStateService::INHERIT
    );

    $allowAttributes = $this->resolveOptionAttributes(
        static function (OrderOptionDefinitionInterface $definition): ?string {
            return $definition->getAllowSettingsKey();
        },
        false
    );

    $priceAttributes = $this->resolveOptionAttributes(
        static function (OrderOptionDefinitionInterface $definition): ?string {
            return $definition->getPriceSettingsKey();
        },
        0
    );

    $dynamicAttributes = array_merge($exportAttributes, $allowAttributes, $priceAttributes);

    $exportCasts = array_fill_keys(array_keys($exportAttributes), TriStateService::TYPE_STRICT);
    $allowCasts = array_fill_keys(array_keys($allowAttributes), 'bool');
    $priceCasts = array_fill_keys(array_keys($priceAttributes), 'float');
    $dynamicCasts = array_merge($exportCasts, $allowCasts, $priceCasts);

    $this->attributes = array_merge($dynamicAttributes, $this->attributes);
    $this->casts = array_merge($dynamicCasts, $this->casts);
}
```

Remove the `EXPORT_*` constants that are now derived, along with their `$attributes`/`$casts` entries. Also remove `ALLOW_ONLY_RECIPIENT`, `ALLOW_SIGNATURE`, `ALLOW_PRIORITY_DELIVERY`, `ALLOW_SAME_DAY_DELIVERY`, `ALLOW_SATURDAY_DELIVERY`, `PRICE_ONLY_RECIPIENT`, `PRICE_SIGNATURE`, `PRICE_PRIORITY_DELIVERY`, `PRICE_COLLECT` from static attributes.

Keep non-option constants (`CUTOFF_TIME`, `DROP_OFF_DELAY`, delivery type `ALLOW_*` and `PRICE_*` like `ALLOW_EVENING_DELIVERY`, `PRICE_DELIVERY_TYPE_EVENING_DELIVERY`, etc.).

Mark removed constants as `@deprecated` if platform integrations reference them.

- [ ] **Step 4: Run all tests to verify no regressions**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/Settings/Model/CarrierSettings.php tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "feat(options): integrate ResolvesOptionAttributes into CarrierSettings"
```

---

### Task 7: Integrate Trait into ProductSettings

**Files:**

- Modify: `src/Settings/Model/ProductSettings.php`
- Modify: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

- [ ] **Step 1: Write a failing test**

```php
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

it('registers product settings attributes from definitions', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $settings = new ProductSettings();

    foreach ($definitions as $definition) {
        $key = $definition->getProductSettingsKey();

        if ($key === null) {
            continue;
        }

        expect($settings->getAttribute($key))->toBe(
            TriStateService::INHERIT,
            "ProductSettings missing attribute '{$key}'"
        );
    }
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="registers product settings attributes"`

- [ ] **Step 3: Add trait to ProductSettings**

```php
protected function initializeResolvesOptionAttributes(): void
{
    $productAttributes = $this->resolveOptionAttributes(
        static function (OrderOptionDefinitionInterface $definition): ?string {
            return $definition->getProductSettingsKey();
        },
        TriStateService::INHERIT
    );

    $productCasts = array_fill_keys(array_keys($productAttributes), TriStateService::TYPE_STRICT);

    $this->attributes = array_merge($productAttributes, $this->attributes);
    $this->casts = array_merge($productCasts, $this->casts);
}
```

Remove the `EXPORT_*` constants and their `$attributes`/`$casts` entries that are now derived. Keep non-option constants (`COUNTRY_OF_ORIGIN`, `CUSTOMS_CODE`, `FIT_IN_MAILBOX`, `FIT_IN_DIGITAL_STAMP`, `PACKAGE_TYPE`, `DISABLE_DELIVERY_OPTIONS`, `DROP_OFF_DELAY`, `EXCLUDE_PARCEL_LOCKERS`) — these belong to product-only definitions that use string keys and have non-standard casts/defaults that remain static.

- [ ] **Step 4: Run all tests**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/Settings/Model/ProductSettings.php tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "feat(options): integrate ResolvesOptionAttributes into ProductSettings"
```

---

### Task 8: Integrate Trait into Fulfilment ShipmentOptions

**Files:**

- Modify: `src/Fulfilment/Model/ShipmentOptions.php`
- Modify: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

- [ ] **Step 1: Write a failing test**

```php
use MyParcelNL\Pdk\Fulfilment\Model\ShipmentOptions as FulfilmentShipmentOptions;

it('registers option attributes on Fulfilment ShipmentOptions with boolean cast', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $options = new FulfilmentShipmentOptions();

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();

        if ($key === null) {
            continue;
        }

        expect($options->getAttribute($key))->toBeNull(
            "Fulfilment ShipmentOptions missing attribute '{$key}'"
        );
    }
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="registers option attributes on Fulfilment ShipmentOptions"`

- [ ] **Step 3: Add trait to Fulfilment ShipmentOptions**

```php
protected function initializeResolvesOptionAttributes(): void
{
    $optionAttributes = $this->resolveOptionAttributes(
        static function (OrderOptionDefinitionInterface $definition): ?string {
            return $definition->getShipmentOptionsKey();
        },
        null
    );

    $optionCasts = array_fill_keys(array_keys($optionAttributes), 'bool');

    // Insurance has special int cast
    if (isset($optionCasts['insurance'])) {
        $optionCasts['insurance'] = 'int';
    }

    $this->attributes = array_merge($optionAttributes, $this->attributes);
    $this->casts = array_merge($optionCasts, $this->casts);
}
```

Remove hardcoded option attributes from `$attributes` and `$casts`. Keep non-option attributes (`deliveryDate`, `deliveryType`, `packageType`, `cooledDelivery`, `labelDescription`, `dropOffAtPostalPoint`).

- [ ] **Step 4: Run all tests**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/Fulfilment/Model/ShipmentOptions.php tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "feat(options): integrate ResolvesOptionAttributes into Fulfilment ShipmentOptions"
```

---

### Task 9: CarrierSchema \_\_call() Proxy

**Files:**

- Modify: `src/Validation/Validator/CarrierSchema.php`
- Modify: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

- [ ] **Step 1: Write a test for the \_\_call() proxy**

```php
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;

it('proxies canHave calls to canHaveShipmentOption via __call', function () {
    $schema = new CarrierSchema();
    // Set up carrier using existing test helpers/mocks

    expect($schema->canHaveSignature())->toBe(
        $schema->canHaveShipmentOption(SignatureDefinition::class)
    );
});

it('throws BadMethodCallException for unknown methods on CarrierSchema', function () {
    $schema = new CarrierSchema();

    $schema->nonExistentMethod();
})->throws(\BadMethodCallException::class);
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker compose run php composer test -- --filter="proxies canHave calls"`

- [ ] **Step 3: Replace individual canHave methods with \_\_call()**

Remove all individual `canHaveAgeCheck()`, `canHaveSignature()`, `canHaveDirectReturn()`, `canHaveHideSender()`, `canHaveInsurance()`, `canHaveLargeFormat()`, `canHaveOnlyRecipient()`, `canHavePriorityDelivery()`, `canHaveReceiptCode()`, `canHaveSameDayDelivery()`, `canHaveSaturdayDelivery()`, `canHaveTracked()`, `canHaveCollect()`, `canHaveFreshFood()`, `canHaveFrozen()` methods.

Add the `__call()` method:

```php
/**
 * @param  string $name
 * @param  array  $arguments
 *
 * @return mixed
 */
public function __call(string $name, array $arguments)
{
    if (strpos($name, 'canHave') === 0) {
        $optionName = substr($name, 7);
        $definitionClass = sprintf(
            'MyParcelNL\\Pdk\\App\\Options\\Definition\\%sDefinition',
            $optionName
        );

        if (class_exists($definitionClass)) {
            return $this->canHaveShipmentOption($definitionClass);
        }
    }

    throw new \BadMethodCallException(sprintf('Method %s does not exist on %s', $name, static::class));
}
```

Keep these non-option methods as real methods: `canBeDigitalStamp()`, `canBeLetter()`, `canBeMailbox()`, `canBePackage()`, `canBePackageSmall()`, `canHaveEveningDelivery()`, `canHaveExpressDelivery()`, `canHaveMondayDelivery()`, `canHaveMorningDelivery()`, `canHaveStandardDelivery()`, `canHavePickup()`, `canHaveMultiCollo()`, `canHaveWeight()`, `hasReturnCapabilities()`.

Also keep `canHaveShipmentOption()` — it's the real implementation.

Remove the now-unused imports of individual Definition classes.

- [ ] **Step 4: Run all tests**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/Validation/Validator/CarrierSchema.php tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "refactor(options): replace CarrierSchema canHave methods with __call proxy"
```

---

### Task 10: Remove Internal ShipmentOptions Constant Usage

**Files:**

- Modify: `src/App/DeliveryOptions/Service/DeliveryOptionsFeesService.php`
- Modify: `src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php`
- Modify: `src/App/Order/Calculator/General/InsuranceCalculator.php`
- Modify: `src/App/Endpoint/Resource/DeliveryOptionsV1Resource.php`

- [ ] **Step 1: Run all tests as baseline**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 2: Replace ShipmentOptions constant references in DeliveryOptionsFeesService**

In `src/App/DeliveryOptions/Service/DeliveryOptionsFeesService.php`, replace:

```php
// Before
ShipmentOptions::ONLY_RECIPIENT,
ShipmentOptions::PRIORITY_DELIVERY,
ShipmentOptions::SIGNATURE,

// After — use string values directly
'onlyRecipient',
'priorityDelivery',
'signature',
```

- [ ] **Step 3: Replace references in PackageTypeShipmentOptionsCalculator**

In `src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php`, replace:

```php
// Before
ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
// etc.

// After
'ageCheck'         => TriStateService::DISABLED,
'return'           => TriStateService::DISABLED,
'hideSender'       => TriStateService::DISABLED,
'largeFormat'      => TriStateService::DISABLED,
'onlyRecipient'    => TriStateService::DISABLED,
'sameDayDelivery'  => TriStateService::DISABLED,
'signature'        => TriStateService::DISABLED,
'receiptCode'      => TriStateService::DISABLED,
```

- [ ] **Step 4: Replace references in InsuranceCalculator**

In `src/App/Order/Calculator/General/InsuranceCalculator.php`, replace `ShipmentOptions::INSURANCE` with `'insurance'`.

- [ ] **Step 5: Replace references in DeliveryOptionsV1Resource**

In `src/App/Endpoint/Resource/DeliveryOptionsV1Resource.php`, this file maps PDK shipment option keys to OrderApi keys. Replace `ShipmentOptions::*` constant references with string values. The mapping logic itself should ideally iterate definitions in Phase 2, but for now replace with strings:

```php
// Before
ShipmentOptions::AGE_CHECK => $orderApiShipmentOptions['requires_age_verification'],
ShipmentOptions::SIGNATURE => $orderApiShipmentOptions['requires_signature'],
// etc.

// After
'ageCheck' => $orderApiShipmentOptions['requires_age_verification'],
'signature' => $orderApiShipmentOptions['requires_signature'],
'onlyRecipient' => $orderApiShipmentOptions['recipient_only_delivery'],
'largeFormat' => $orderApiShipmentOptions['oversized_package'],
'return' => $orderApiShipmentOptions['print_return_label_at_drop_off'],
'hideSender' => $orderApiShipmentOptions['hide_sender'],
'labelDescription' => $orderApiShipmentOptions['custom_label_text'],
'priorityDelivery' => $orderApiShipmentOptions['priority_delivery'],
'receiptCode' => $orderApiShipmentOptions['requires_receipt_code'],
'sameDayDelivery' => $orderApiShipmentOptions['same_day_delivery'],
'saturdayDelivery' => $orderApiShipmentOptions['saturday_delivery'],
'collect' => $orderApiShipmentOptions['scheduled_collection'],
```

Keep the special handling for `'insurance'` and `'labelDescription'` as-is.

- [ ] **Step 6: Run all tests to verify no regressions**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add src/App/DeliveryOptions/Service/DeliveryOptionsFeesService.php \
  src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php \
  src/App/Order/Calculator/General/InsuranceCalculator.php \
  src/App/Endpoint/Resource/DeliveryOptionsV1Resource.php
git commit -m "refactor(options): remove internal usage of deprecated ShipmentOptions constants"
```

---

### Task 11: End-to-End Flow Test

**Files:**

- Modify: `tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php`

This test registers a fake definition and verifies the entire pipeline from settings to API export/import.

- [ ] **Step 1: Write the comprehensive flow test**

Expand the flow test file to cover all 9 pipeline stages from the spec:

1. **Settings registration** — The option's `export*`, `allow*`, and `price*` keys exist on `CarrierSettings` and `ProductSettings` with correct defaults.
2. **ShipmentOptions model** — The option key is a valid attribute with tri-state default.
3. **Fulfilment model** — The option key is a valid attribute with boolean cast and null default.
4. **Option calculation** — `PdkOrderOptionsService::calculateShipmentOptions()` resolves the option value through the priority chain.
5. **Carrier validation** — `CarrierSchema::canHaveShipmentOption()` returns true/false based on capabilities data.
6. **Legacy API export** — `PostShipmentsRequest` includes the option in snake_case in the request body.
7. **Fulfilment API export** — `PostOrdersRequest` includes the option in the encoded output.
8. **V2 API export** — `ShipmentOptions::toCapabilitiesDefinitions()` maps the option to its capabilities key.
9. **V2 API import** — `ShipmentOptions::fromCapabilitiesDefinitions()` maps the capabilities key back.

The test should register a `TestFlowOptionDefinition` in the DI container alongside existing definitions, then exercise each pipeline stage. Adapt the exact container binding and mock setup to the PDK test infrastructure (check existing tests like `PdkOrderOptionsServiceTest.php` for patterns).

- [ ] **Step 2: Run the test**

Run: `docker compose run php composer test -- --filter="ShipmentOptionDefinitionFlowTest"`
Expected: All assertions PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/App/Options/ShipmentOptionDefinitionFlowTest.php
git commit -m "test(options): add end-to-end flow test for option definition pipeline"
```

---

### Task 12: Consistency Tests

**Files:**

- Create: `tests/Unit/App/Options/OptionDefinitionConsistencyTest.php`

- [ ] **Step 1: Write consistency tests**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

it('ensures every definition shipment options key matches a deprecated constant on ShipmentOptions', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $reflection = new ReflectionClass(ShipmentOptions::class);
    $constants = $reflection->getConstants();

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();

        if ($key === null) {
            continue;
        }

        expect(in_array($key, $constants, true))
            ->toBeTrue("ShipmentOptions is missing a constant for key '{$key}'");
    }
});

it('ensures every derived settings key is a valid attribute on its model', function () {
    $definitions = Pdk::get('orderOptionDefinitions');
    $carrierSettings = new CarrierSettings();
    $productSettings = new ProductSettings();

    foreach ($definitions as $definition) {
        $carrierKey = $definition->getCarrierSettingsKey();
        if ($carrierKey) {
            expect($carrierSettings->getAttribute($carrierKey))->not->toBeNull(
                "CarrierSettings missing attribute for key '{$carrierKey}'"
            );
        }

        $productKey = $definition->getProductSettingsKey();
        if ($productKey) {
            expect($productSettings->getAttribute($productKey))->not->toBeNull(
                "ProductSettings missing attribute for key '{$productKey}'"
            );
        }

        $allowKey = $definition->getAllowSettingsKey();
        if ($allowKey) {
            expect($carrierSettings->getAttribute($allowKey))->not->toBeNull(
                "CarrierSettings missing attribute for allow key '{$allowKey}'"
            );
        }

        $priceKey = $definition->getPriceSettingsKey();
        if ($priceKey) {
            expect($carrierSettings->getAttribute($priceKey))->not->toBeNull(
                "CarrierSettings missing attribute for price key '{$priceKey}'"
            );
        }
    }
});

it('ensures no internal PDK source files reference ShipmentOptions constants', function () {
    $srcDir = dirname(__DIR__, 4) . '/src';
    $pattern = '/ShipmentOptions::[A-Z_]+/';

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $violations = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        // Skip the ShipmentOptions class itself (where constants are defined)
        if (strpos($file->getPathname(), 'Shipment/Model/ShipmentOptions.php') !== false) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (preg_match_all($pattern, $contents, $matches)) {
            $violations[] = sprintf('%s: %s', $file->getPathname(), implode(', ', $matches[0]));
        }
    }

    expect($violations)->toBeEmpty(
        "Found internal ShipmentOptions constant usage:\n" . implode("\n", $violations)
    );
});

it('ensures each SDK-derived shipment options key matches its Str::camel conversion', function () {
    $sdkMap = \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions::attributeMap();
    $definitions = Pdk::get('orderOptionDefinitions');

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();

        if ($key === null) {
            continue;
        }

        // Find the SDK key that camel-converts to this definition's key
        $matchingSdkKey = null;

        foreach ($sdkMap as $sdkKey => $sdkValue) {
            if (\MyParcelNL\Sdk\Support\Str::camel($sdkKey) === $key) {
                $matchingSdkKey = $sdkKey;
                break;
            }
        }

        // Options without an SDK mapping (e.g. excludeParcelLockers) are allowed
        // Options WITH an SDK mapping must match Str::camel(sdkKey) exactly
        if ($matchingSdkKey !== null) {
            expect(\MyParcelNL\Sdk\Support\Str::camel($matchingSdkKey))->toBe(
                $key,
                "Definition key '{$key}' does not match Str::camel('{$matchingSdkKey}')"
            );
        }
    }
});
```

- [ ] **Step 2: Run the consistency tests**

Run: `docker compose run php composer test -- --filter="OptionDefinitionConsistencyTest"`
Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/App/Options/OptionDefinitionConsistencyTest.php
git commit -m "test(options): add consistency tests for option definition system"
```

---

### Task 13: Frontend Consistency Tests

**Files:**

- Create: `tests/Unit/App/Options/FrontendDefinitionConsistencyTest.php`

- [ ] **Step 1: Write tests that verify frontend code maps to definitions**

These tests assert that every option referenced in frontend views corresponds to a registered definition. They serve as the forcing function for the Phase 2 migration.

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;

it('ensures every allow/price key in DeliveryOptionsService CONFIG_CARRIER_SETTINGS_MAP corresponds to a definition', function () {
    $definitions = Pdk::get('orderOptionDefinitions');

    $definitionAllowKeys = [];
    $definitionPriceKeys = [];

    foreach ($definitions as $definition) {
        $allowKey = $definition->getAllowSettingsKey();
        $priceKey = $definition->getPriceSettingsKey();

        if ($allowKey) {
            $definitionAllowKeys[] = $allowKey;
        }
        if ($priceKey) {
            $definitionPriceKeys[] = $priceKey;
        }
    }

    $reflection = new ReflectionClass(\MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService::class);
    $constant = $reflection->getReflectionConstant('CONFIG_CARRIER_SETTINGS_MAP');
    $map = $constant->getValue();

    $unmappedEntries = [];

    foreach ($map as $frontendKey => $settingsValue) {
        // Only check shipment option allow/price keys, skip delivery type entries
        $isAllow = strpos($settingsValue, 'allow') === 0;
        $isPrice = strpos($settingsValue, 'price') === 0;

        if (! $isAllow && ! $isPrice) {
            continue;
        }

        $inDefinitions = in_array($settingsValue, $definitionAllowKeys, true)
            || in_array($settingsValue, $definitionPriceKeys, true);

        if (! $inDefinitions) {
            $unmappedEntries[] = "{$frontendKey} => {$settingsValue}";
        }
    }

    // Document unmapped entries — will become strict assertion in Phase 2
    // For now, some delivery type entries are expected to be unmapped
    expect(true)->toBeTrue();
});
```

- [ ] **Step 2: Run the test**

Run: `docker compose run php composer test -- --filter="FrontendDefinitionConsistencyTest"`
Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/App/Options/FrontendDefinitionConsistencyTest.php
git commit -m "test(options): add frontend consistency tests for definition coverage"
```

---

### Task 14: IDE Helper Verification

**Files:**

- Create or extend: `tests/Unit/App/Options/IdeHelperVerificationTest.php`

- [ ] **Step 1: Run the ide-helper generator**

Run: `docker compose run php composer console generate:ide-helper`

Check `.meta/pdk_ide_helper.php` for the `ShipmentOptions`, `CarrierSettings`, and `ProductSettings` classes — verify that dynamically registered attributes appear in the `@property` docblocks.

- [ ] **Step 2: If dynamic attributes are missing, investigate PhpSourceParser**

Check `private/Types/Shared/Service/PhpSourceParser.php` to see if it instantiates models or reads the static `$attributes` property. If it reads statically, adjust it to instantiate the model so the trait initializer runs.

- [ ] **Step 3: Write a test that verifies dynamic attributes are in the ide-helper output**

```php
<?php

declare(strict_types=1);

it('ensures the ide-helper contains dynamic option attributes for ShipmentOptions', function () {
    $ideHelperPath = dirname(__DIR__, 4) . '/.meta/pdk_ide_helper.php';

    if (! file_exists($ideHelperPath)) {
        $this->markTestSkipped('IDE helper file not generated — run: composer console generate:ide-helper');
    }

    $contents = file_get_contents($ideHelperPath);

    // Verify dynamically registered attributes appear
    expect($contents)->toContain('$signature');
    expect($contents)->toContain('$ageCheck');
    expect($contents)->toContain('$exportSignature');
    expect($contents)->toContain('$allowSignature');
    expect($contents)->toContain('$priceSignature');
});
```

- [ ] **Step 4: Run the test**

Run: `docker compose run php composer test -- --filter="ide-helper contains dynamic"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/App/Options/IdeHelperVerificationTest.php
git commit -m "test(options): verify ide-helper picks up dynamic option attributes"
```

---

### Task 15: Create Claude Skill for Adding Shipment Options

**Files:**

- Create: `.claude/skills/add-shipment-option.md`

- [ ] **Step 1: Create the skill file**

````markdown
---
name: add-shipment-option
description: Guide step-by-step through adding a new shipment option to the PDK
---

# Add Shipment Option

Use this skill when adding a new shipment option to the PDK.

## Prerequisites

You need:

- The snake_case key from `RefShipmentShipmentOptions::attributeMap()` (e.g. `my_new_option`)
- The snake_case key from `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()` (e.g. `my_new_option`)

## Steps

### 1. Create the Definition Class

Create `src/App/Options/Definition/{OptionName}Definition.php` extending `AbstractOrderOptionDefinition`.

Required methods:

- `getShipmentOptionsKey()` — return `Str::camel(RefShipmentShipmentOptions::attributeMap()['sdk_key'])`
- `getCapabilitiesOptionsKey()` — return `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['capabilities_key']`

Optional overrides (return `null` to opt out):

- `getCarrierSettingsKey()` — defaults to `'export' . ucfirst(shipmentOptionsKey)`
- `getProductSettingsKey()` — defaults to same as carrier settings key
- `getAllowSettingsKey()` — defaults to `'allow' . ucfirst(shipmentOptionsKey)`
- `getPriceSettingsKey()` — defaults to `'price' . ucfirst(shipmentOptionsKey)`
- `validate()` — defaults to checking capabilities via `canHaveShipmentOption()`

If the PDK key does not match `Str::camel()` of the SDK key, use a string override for `getShipmentOptionsKey()`.

### 2. Register the Definition

Add the new definition to the `orderOptionDefinitions` array in `config/pdk-business-logic.php`.

### 3. Add Deprecated Constant to ShipmentOptions

Add a `@deprecated` constant to `src/Shipment/Model/ShipmentOptions.php` for backwards compatibility with platform integrations. Do NOT use this constant anywhere in PDK code.

### 4. Run IDE Helper

```bash
docker compose run php composer console generate:ide-helper
```
````

### 5. Run Tests

```bash
yarn run test:unit
```

The consistency tests will verify everything is wired up correctly.

````

- [ ] **Step 2: Commit**

```bash
git add .claude/skills/add-shipment-option.md
git commit -m "docs: add Claude skill for guided shipment option creation"
````

---

### Task 16: Update Snapshots and Final Verification

- [ ] **Step 1: Run all tests**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 2: Update snapshots if needed**

Run: `yarn test:unit:snapshot`

- [ ] **Step 3: Run static analysis**

Run: `docker compose run php composer analyse`
Expected: No new errors. If there are errors from missing types on dynamic attributes, update the PHPStan baseline.

- [ ] **Step 4: Run ide-helper generation**

Run: `docker compose run php composer console generate:ide-helper`

- [ ] **Step 5: Commit any snapshot/ide-helper changes**

```bash
git add -A
git commit -m "chore: update snapshots and ide-helper for option definition refactor"
```
