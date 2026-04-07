# Shipment Options Dynamic Frontend Implementation Plan (Phase 2)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make frontend views and delivery options service iterate registered definitions instead of hardcoding option lists, so adding a new shipment option requires zero view/service changes.

**Architecture:** Each of the three frontend locations (carrier settings view, product settings view, delivery options service) replaces its hardcoded option list with a loop over `Pdk::get('orderOptionDefinitions')`. Insurance remains a special case via `instanceof InsuranceDefinition`. Inter-option UI dependencies (age check → signature/only recipient) stay as explicit view logic.

**Tech Stack:** PHP 7.4+, Pest v1, Docker Compose for test execution

**Spec:** `docs/superpowers/specs/2026-04-03-shipment-options-dynamic-frontend-design.md`

**Test commands:**

- Run all tests: `yarn run test:unit`
- Run filtered test: `docker compose run php composer test -- --filter="test name"`
- Update snapshots: `yarn test:unit:snapshot`

---

### Task 1: Dynamic getShipmentOptionsSettings()

**Files:**

- Modify: `src/Frontend/View/CarrierSettingsItemView.php`

Replace the hardcoded 3-option method with a loop over definitions.

- [ ] **Step 1: Replace getShipmentOptionsSettings() with definition-driven loop**

The current method (lines 555-590) hardcodes signature, only recipient, and priority delivery. Replace with:

```php
/**
 * Build shipment option settings (allow/price toggles) for the delivery options section.
 * Iterates registered definitions — only includes options the consumer can toggle at checkout.
 */
private function getShipmentOptionsSettings(): array
{
    /** @var OrderOptionDefinitionInterface[] $definitions */
    $definitions = Pdk::get('orderOptionDefinitions');
    $settings    = [];

    foreach ($definitions as $definition) {
        $allowKey = $definition->getAllowSettingsKey();
        $priceKey = $definition->getPriceSettingsKey();

        if (! $allowKey || ! $this->carrierSchema->canHaveShipmentOption($definition)) {
            continue;
        }

        if ($priceKey) {
            $elements = $this->createSettingWithPriceFields($allowKey, $priceKey);
        } else {
            $elements = [new InteractiveElement($allowKey, Components::INPUT_TOGGLE)];
        }

        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if ($capabilitiesKey) {
            $this->makeReadOnlyWhenRequired($elements[0], $capabilitiesKey);
        }

        $settings = array_merge($settings, $elements);
    }

    return $settings;
}
```

Add the import (if not already present):

```php
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
```

- [ ] **Step 2: Run tests**

Run: `yarn run test:unit`
If snapshot changes: `yarn test:unit:snapshot` — then verify snapshots only changed in key order, not values.

- [ ] **Step 3: Commit**

```bash
git add src/Frontend/View/CarrierSettingsItemView.php tests/__snapshots__/
git commit -m "refactor(options): make getShipmentOptionsSettings definition-driven"
```

---

### Task 2: Dynamic getDefaultExportFields()

**Files:**

- Modify: `src/Frontend/View/CarrierSettingsItemView.php`

Replace the hardcoded ~11 export toggles with a definition-driven loop. Keep the insurance special case and inter-option dependencies.

- [ ] **Step 1: Replace the individual canHave checks with a definition loop**

The current method (lines 225-325) has individual `if` blocks per option. Replace with a loop that declares special-cased definitions up front, handles them explicitly, then runs a generic loop that skips them:

```php
/**
 * Build the default export settings section.
 * Iterates registered definitions for export toggles, with explicit handling for
 * age check (inter-option dependencies), signature/onlyRecipient (read-only when age check),
 * and insurance (custom SELECT UI instead of toggle).
 */
private function getDefaultExportFields(): array
{
    /** @var OrderOptionDefinitionInterface[] $definitions */
    $definitions = Pdk::get('orderOptionDefinitions');

    // Definitions with custom UI handling — processed explicitly below, then excluded from the generic loop
    $specialCasedDefinitions = [
        AgeCheckDefinition::class,
        SignatureDefinition::class,
        OnlyRecipientDefinition::class,
        InsuranceDefinition::class,
    ];

    // Resolve the special-cased definition instances from the registered definitions
    $ageCheckDefinition      = null;
    $signatureDefinition     = null;
    $onlyRecipientDefinition = null;

    foreach ($definitions as $definition) {
        if ($definition instanceof AgeCheckDefinition) {
            $ageCheckDefinition = $definition;
        } elseif ($definition instanceof SignatureDefinition) {
            $signatureDefinition = $definition;
        } elseif ($definition instanceof OnlyRecipientDefinition) {
            $onlyRecipientDefinition = $definition;
        }
    }

    $fields = [
        new SettingsDivider($this->createGenericLabel('export')),
    ];

    // Age check toggle with afterUpdate logic (forces signature + only recipient on)
    if ($ageCheckDefinition && $this->carrierSchema->canHaveShipmentOption($ageCheckDefinition)) {
        $signatureKey     = $signatureDefinition ? $signatureDefinition->getCarrierSettingsKey() : null;
        $onlyRecipientKey = $onlyRecipientDefinition ? $onlyRecipientDefinition->getCarrierSettingsKey() : null;

        $ageCheckElement = (new InteractiveElement($ageCheckDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE))
            ->builder(function (FormOperationBuilder $builder) use ($signatureKey, $onlyRecipientKey) {
                $builder->afterUpdate(function (FormAfterUpdateBuilder $afterUpdate) use ($signatureKey, $onlyRecipientKey) {
                    if ($signatureKey) {
                        $afterUpdate->setValue(true)->on($signatureKey)->if->eq(true);
                    }
                    if ($onlyRecipientKey) {
                        $afterUpdate->setValue(true)->on($onlyRecipientKey)->if->eq(true);
                    }
                });
            });
        $this->makeReadOnlyWhenRequired($ageCheckElement, $ageCheckDefinition->getCapabilitiesOptionsKey());
        $fields[] = [$ageCheckElement];
    }

    // Signature and only recipient — read-only when age check is enabled
    $signatureElements     = [];
    $onlyRecipientElements = [];

    if ($signatureDefinition && $this->carrierSchema->canHaveShipmentOption($signatureDefinition)) {
        $signatureElement = new InteractiveElement($signatureDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE);
        $this->makeReadOnlyWhenRequired($signatureElement, $signatureDefinition->getCapabilitiesOptionsKey());
        $signatureElements = [$signatureElement];
    }

    if ($onlyRecipientDefinition && $this->carrierSchema->canHaveShipmentOption($onlyRecipientDefinition)) {
        $onlyRecipientElement = new InteractiveElement($onlyRecipientDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE);
        $this->makeReadOnlyWhenRequired($onlyRecipientElement, $onlyRecipientDefinition->getCapabilitiesOptionsKey());
        $onlyRecipientElements = [$onlyRecipientElement];
    }

    $fields[] = $this->withOperation(
        function (FormOperationBuilder $builder) use ($ageCheckDefinition) {
            if (! $ageCheckDefinition || ! $this->carrierSchema->canHaveShipmentOption($ageCheckDefinition)) {
                return;
            }
            $builder->readOnlyWhen($ageCheckDefinition->getCarrierSettingsKey());
        },
        $signatureElements,
        $onlyRecipientElements
    );

    // Generic loop for all other export options
    foreach ($definitions as $definition) {
        $carrierKey = $definition->getCarrierSettingsKey();

        if (! $carrierKey || ! $this->carrierSchema->canHaveShipmentOption($definition)) {
            continue;
        }

        // Skip definitions handled explicitly above
        if (in_array(get_class($definition), $specialCasedDefinitions, true)) {
            continue;
        }

        $element = new InteractiveElement($carrierKey, Components::INPUT_TOGGLE);

        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if ($capabilitiesKey) {
            $this->makeReadOnlyWhenRequired($element, $capabilitiesKey);
        }

        $fields[] = [$element];
    }

    // Insurance — custom SELECT UI handled after the generic loop
    if ($this->carrierSchema->canHaveInsurance()) {
        $fields[] = $this->getExportInsuranceFields();
    }

    return $fields;
}
```

Add these imports at the top of the file:

```php
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
```

- [ ] **Step 2: Run tests**

Run: `yarn run test:unit`
If snapshot changes: `yarn test:unit:snapshot`

- [ ] **Step 3: Commit**

```bash
git add src/Frontend/View/CarrierSettingsItemView.php tests/__snapshots__/
git commit -m "refactor(options): make getDefaultExportFields definition-driven"
```

---

### Task 3: Dynamic ProductSettingsView export options

**Files:**

- Modify: `src/Frontend/View/ProductSettingsView.php`

Replace the hardcoded 8 export option list with a definition loop.

- [ ] **Step 1: Replace hardcoded export elements with definition loop**

In `createElements()`, replace the export options section (lines 90-98) with a call to a new method:

```php
/**
 * Export options.
 */
new SettingsDivider($this->getSettingKey('export_options')),
...$this->getExportOptionElements(),
```

Add the new method:

```php
/**
 * Build export option elements from registered definitions.
 * Only includes definitions with both a product settings key and a shipment options key —
 * product-only settings (countryOfOrigin, packageType, etc.) are rendered in dedicated sections.
 *
 * @return InteractiveElement[]
 */
private function getExportOptionElements(): array
{
    /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions */
    $definitions = Pdk::get('orderOptionDefinitions');
    $elements    = [];

    foreach ($definitions as $definition) {
        $productKey  = $definition->getProductSettingsKey();
        $shipmentKey = $definition->getShipmentOptionsKey();

        if (! $productKey || ! $shipmentKey) {
            continue;
        }

        $elements[] = new InteractiveElement($productKey, Components::INPUT_TRI_STATE);
    }

    return $elements;
}
```

Add the import:

```php
use MyParcelNL\Pdk\Facade\Pdk;
```

Remove the now-unused `ProductSettings` import if no other references remain.

- [ ] **Step 2: Run tests**

Run: `yarn run test:unit`
If snapshot changes: `yarn test:unit:snapshot`

- [ ] **Step 3: Commit**

```bash
git add src/Frontend/View/ProductSettingsView.php tests/__snapshots__/
git commit -m "refactor(options): make ProductSettingsView export options definition-driven"
```

---

### Task 4: Dynamic DeliveryOptionsService carrier settings map

**Files:**

- Modify: `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`

Replace `CONFIG_CARRIER_SETTINGS_MAP` with a split: static constant for non-definition entries, dynamic method for the full map.

- [ ] **Step 1: Rename constant, remove shipment option entries, add dynamic method**

Rename `CONFIG_CARRIER_SETTINGS_MAP` to `NON_DEFINITION_CARRIER_SETTINGS_MAP`. Remove these shipment-option entries (they're now generated from definitions):

- `'allowOnlyRecipient'`, `'allowPriorityDelivery'`, `'allowSameDayDelivery'`, `'allowSaturdayDelivery'`, `'allowSignature'`
- `'priceOnlyRecipient'`, `'pricePriorityDelivery'`, `'priceSignature'`, `'priceCollect'`

Add a comment explaining the constant only covers non-definition entries:

```php
/**
 * Settings map for non-shipment-option entries (delivery types, package types, etc.)
 * that are not covered by OrderOptionDefinitions. Shipment option allow/price keys
 * are built dynamically from definitions in getCarrierSettingsMap().
 */
private const NON_DEFINITION_CARRIER_SETTINGS_MAP = [
    'allowDeliveryOptions'         => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
    'allowStandardDelivery'        => CarrierSettings::ALLOW_STANDARD_DELIVERY,
    'allowEveningDelivery'         => CarrierSettings::ALLOW_EVENING_DELIVERY,
    'allowMondayDelivery'          => CarrierSettings::ALLOW_MONDAY_DELIVERY,
    'allowMorningDelivery'         => CarrierSettings::ALLOW_MORNING_DELIVERY,
    'allowPickupLocations'         => CarrierSettings::ALLOW_PICKUP_DELIVERY,
    'allowExpressDelivery'         => CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS,
    'priceEveningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EVENING_DELIVERY,
    'priceMorningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_MORNING_DELIVERY,
    'pricePackageTypeDigitalStamp' => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
    'pricePackageTypeMailbox'      => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
    'pricePackageTypePackageSmall' => CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL,
    'pricePickup'                  => CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
    'priceSameDayDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY,
    'priceStandardDelivery'        => CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD_DELIVERY,
    'priceExpressDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EXPRESS_DELIVERY,
    'excludeParcelLockers'         => CheckoutSettings::EXCLUDE_PARCEL_LOCKERS,
];
```

Add the dynamic method:

```php
/**
 * Build the full carrier settings map by merging definition-derived allow/price keys
 * with the static non-definition entries.
 *
 * @return array<string, string>
 */
private static function getCarrierSettingsMap(): array
{
    /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions */
    $definitions = Pdk::get('orderOptionDefinitions');
    $map         = [];

    foreach ($definitions as $definition) {
        $allowKey = $definition->getAllowSettingsKey();

        if ($allowKey) {
            $map[$allowKey] = $allowKey;
        }

        $priceKey = $definition->getPriceSettingsKey();

        if ($priceKey) {
            $map[$priceKey] = $priceKey;
        }
    }

    return array_merge($map, self::NON_DEFINITION_CARRIER_SETTINGS_MAP);
}
```

- [ ] **Step 2: Replace all references to CONFIG_CARRIER_SETTINGS_MAP**

Find all usages of `self::CONFIG_CARRIER_SETTINGS_MAP` in the file and replace with `self::getCarrierSettingsMap()`.

Add the import:

```php
use MyParcelNL\Pdk\Facade\Pdk;
```

- [ ] **Step 3: Run tests**

Run: `yarn run test:unit`
If snapshot changes: `yarn test:unit:snapshot`

- [ ] **Step 4: Commit**

```bash
git add src/App/DeliveryOptions/Service/DeliveryOptionsService.php tests/__snapshots__/
git commit -m "refactor(options): make DeliveryOptionsService carrier settings map definition-driven"
```

---

### Task 5: Strengthen FrontendDefinitionConsistencyTest

**Files:**

- Modify: `tests/Unit/App/Options/FrontendDefinitionConsistencyTest.php`

- [ ] **Step 1: Update the test to assert zero shipment option entries in the static map**

Replace the current test with one that verifies the renamed `NON_DEFINITION_CARRIER_SETTINGS_MAP` contains no entries that overlap with definition allow/price keys:

```php
/**
 * Ensures the static NON_DEFINITION_CARRIER_SETTINGS_MAP does not contain any entries
 * that are covered by registered definitions. If a definition provides an allow/price key,
 * it should NOT appear in the static map — it will be included dynamically.
 */

usesShared(new UsesEachMockPdkInstance());

it('ensures NON_DEFINITION_CARRIER_SETTINGS_MAP contains no shipment option entries', function () {
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

    $reflection = new ReflectionClass(DeliveryOptionsService::class);
    $constants  = $reflection->getConstants();
    $map        = $constants['NON_DEFINITION_CARRIER_SETTINGS_MAP'];

    $violations = [];

    foreach ($map as $frontendKey => $settingsValue) {
        $inDefinitions = in_array($settingsValue, $definitionAllowKeys, true)
            || in_array($settingsValue, $definitionPriceKeys, true);

        if ($inDefinitions) {
            $violations[] = "{$frontendKey} => {$settingsValue}";
        }
    }

    expect($violations)->toBeEmpty(
        'These entries should be removed from NON_DEFINITION_CARRIER_SETTINGS_MAP — they are covered by definitions: '
        . implode(', ', $violations)
    );
});
```

- [ ] **Step 2: Run tests**

Run: `yarn run test:unit`
Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/App/Options/FrontendDefinitionConsistencyTest.php
git commit -m "test(options): strengthen frontend consistency test for Phase 2"
```

---

### Task 6: Final verification

- [ ] **Step 1: Run all tests**

Run: `yarn run test:unit`
Expected: All tests pass.

- [ ] **Step 2: Run static analysis**

Run: `docker compose run php composer analyse`
Expected: No new errors.

- [ ] **Step 3: Update snapshots and ide-helper if needed**

Run: `yarn test:unit:snapshot`
Run: `docker compose run php composer console generate:ide-helper`

- [ ] **Step 4: Commit any remaining changes**

```bash
git add -A
git commit -m "chore: update snapshots and ide-helper for Phase 2 dynamic frontend"
```
