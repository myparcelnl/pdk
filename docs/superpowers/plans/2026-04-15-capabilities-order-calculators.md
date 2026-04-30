# Replace Carrier-Specific Calculators with Capabilities API

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace hardcoded carrier-specific order calculators with a generic capabilities-driven approach, so shipment option constraints (`requires`/`excludes`/`isRequired`), package type validation, and insurance tiers are determined at runtime from the API instead of per-carrier PHP classes.

**Architecture:** Two new generic calculators replace all carrier-specific logic: `CapabilitiesOptionCalculator` applies option constraints from the contextual capabilities API response, and `CapabilitiesPackageTypeCalculator` validates package types and falls back to the next available type when needed. `InsuranceCalculator` is modified to derive tiers from capabilities instead of JSON schemas. Carrier-specific calculator directories are removed.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models, `CapabilitiesValidationService` (from Plan 1), Guzzle MockHandler

**Jira:** [INT-1501](https://myparcelnl.atlassian.net/browse/INT-1501) (sub-task of INT-930)

**Branch:** `feat/INT-1501-capabilities-order-calculators`

**Depends on:** Plan 1 (`feat/INT-1500-checkout-capabilities-api`) merged first

---

## Design decisions

### Contextual capabilities call for order export

Unlike the checkout (Plan 1), order export has a fully specified context: carrier, recipient, package_type, delivery_type, and options are all known. We call `POST /shipments/capabilities` with the full context and get back precise constraints for this exact shipment configuration. The call is cached per unique parameter set.

### Option constraints from `requires`/`excludes`

Each option in the capabilities response has:

- `isRequired: bool` — option must be enabled
- `isSelectedByDefault: bool` — default if no explicit choice
- `requires: string[]` — other options that must also be enabled
- `excludes: string[]` — other options that cannot be enabled simultaneously

The `CapabilitiesOptionCalculator` reads these and forces options accordingly. This replaces all carrier-specific "if ageCheck then enable signature" logic.

### Carrier settings (allowX) take precedence over capabilities

Options must always be limited by the merchant's carrier-level settings (`allowSignature`, `allowOnlyRecipient`, etc.). If the merchant has disabled an option, it must not be enabled even if capabilities says it's available. The resolution order is:

1. **Carrier settings (allowX)** — merchant decides what options are offered
2. **Capabilities (requires/excludes)** — API decides what's valid for the context
3. **Product/shipment settings** — order-specific values

An option disabled in carrier settings is never enabled by capabilities. An option enabled in carrier settings may still be forced by capabilities `isRequired` or constrained by `requires`/`excludes`.

### Calculator ordering matters

The calculator chain runs sequentially. The order is intentional:

1. `CapabilitiesPackageTypeCalculator` — determines the package type first, since all subsequent calculations depend on it
2. `TriStateOptionCalculator` — resolves tri-state values from settings/product/carrier chain
3. `CapabilitiesOptionCalculator` — applies capabilities constraints on the resolved values
4. Remaining calculators operate on the finalized options

### Exception handlers for API gaps

Some constraints the API cannot express (yet):

- **Delivery date:** The SDK model defines `deliveryDate` on the options type (auto-generated from the OpenAPI spec), but this does not mean the API populates it in practice. Needs verification against the actual API at implementation time. If the API does include/omit it per carrier, the calculator can check for its presence. If not, a simple exception handler is needed.
- **Customer information sharing:** DPD requires customer info regardless of the global setting. No capabilities endpoint exposes this yet.

These stay as small, explicit exception calculators rather than being mixed into the capabilities flow.

---

## Behavioral test matrix

Best tested in **WooCommerce or PrestaShop** — verify export behavior with different carriers and options.

### Existing behavior (must not regress)

| #   | Scenario                                      | Expected                                           |
| --- | --------------------------------------------- | -------------------------------------------------- |
| 1   | PostNL shipment with age check                | Signature and only-recipient automatically enabled |
| 2   | DHL Europlus shipment                         | Signature always required                          |
| 3   | GLS shipment to EU                            | Signature required, insurance coupled              |
| 4   | DHL For You shipment abroad                   | Age check, only-recipient, same-day disabled       |
| 5   | Bpost/DPD shipment                            | Delivery date is empty                             |
| 6   | International mailbox without setting enabled | Package type falls back to default package         |
| 7   | Insurance with specific carrier/country       | Min/max from capabilities, not local schemas       |
| 8   | Non-package type shipment                     | Shipment options constrained by capabilities       |
| 9   | Contract ID on exported shipment              | Present in API request                             |

---

## TODOs resolved by this plan

| File                                | Line         | TODO                                                      | How resolved                                                   |
| ----------------------------------- | ------------ | --------------------------------------------------------- | -------------------------------------------------------------- |
| `InsuranceCalculator.php`           | 25, 204, 215 | INT-930: replace schema-based tiers with capabilities API | Task 3: capabilities min/max/default replaces SchemaRepository |
| `CustomerInformationCalculator.php` | 55           | Specific carrier check for DPD                            | Documented as known exception, stays for now                   |

---

## Context for the implementing agent

### Calculator execution chain

Calculators run in order defined in `config/pdk-business-logic.php` under `orderCalculators`. Each receives the `PdkOrder` and mutates it in-place. Order matters — tri-state resolution runs before carrier-specific logic, which runs before weight calculation.

### How to call capabilities for an order context

Use `CapabilitiesValidationService` (from Plan 1) which wraps `CarrierCapabilitiesRepository`:

```php
$capabilities = $this->capabilitiesValidation->getCapabilitiesForPackageType(
    $order->shippingAddress->cc,
    DeliveryOptions::PACKAGE_TYPES_V2_MAP[$order->deliveryOptions->packageType]
);
$capability = $capabilities[$order->deliveryOptions->carrier->carrier] ?? null;
```

The response includes `options` with `requires`/`excludes`/`isRequired` per option, `physicalProperties` with weight constraints, and `contract` with the contract ID.

### Option key mapping

`OrderOptionDefinitionInterface` maps between PDK shipment option keys and capabilities keys:

- `getShipmentOptionsKey()` → PDK key (e.g. `'signature'`)
- `getCapabilitiesOptionsKey()` → API key (e.g. `'requiresSignature'`)

### Key files

| File                                                                        | Why                           |
| --------------------------------------------------------------------------- | ----------------------------- |
| `src/App/Order/Calculator/General/CarrierSpecificCalculator.php`            | Dispatcher being removed      |
| `src/App/Order/Calculator/General/AllowedInCarrierCalculator.php`           | Being removed                 |
| `src/App/Order/Calculator/General/InsuranceCalculator.php`                  | Being modified                |
| `src/App/Order/Calculator/General/PackageTypeCalculator.php`                | Being replaced                |
| `src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php` | Being replaced                |
| `src/App/Order/Calculator/General/TrackedCalculator.php`                    | Being replaced                |
| `src/Carrier/Service/CapabilitiesValidationService.php`                     | Reused from Plan 1            |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`               | Key mapping                   |
| `config/pdk-business-logic.php`                                             | Calculator chain registration |
| `tests/factories/Carrier/Model/CarrierFactory.php`                          | Test carrier setup            |

### Test infrastructure

- Tests via Docker: `docker compose run php composer test -- --filter="test name"`
- Mock capabilities via `MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([...]))`
- Reset storage cache after `factory(Shop::class)->store()` in tests that set specific carriers
- Pest v1 only

---

## File Structure

| File                                                                        | Action | Responsibility                                                                 |
| --------------------------------------------------------------------------- | ------ | ------------------------------------------------------------------------------ |
| `src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php`         | Create | Apply `requires`/`excludes`/`isRequired` from capabilities to shipment options |
| `src/App/Order/Calculator/General/CapabilitiesPackageTypeCalculator.php`    | Create | Validate package type, fall back to next available type from capabilities      |
| `src/App/Order/Calculator/General/DeliveryDateExceptionCalculator.php`      | Create | Null delivery date for carriers that don't support it                          |
| `src/App/Order/Calculator/General/InsuranceCalculator.php`                  | Modify | Replace SchemaRepository with capabilities for tier resolution                 |
| `src/Carrier/Service/CapabilitiesValidationService.php`                     | Modify | Add method to fetch capabilities for a full order context                      |
| `config/pdk-business-logic.php`                                             | Modify | Update calculator chain                                                        |
| `src/App/Order/Calculator/General/CarrierSpecificCalculator.php`            | Remove | Replaced by CapabilitiesOptionCalculator                                       |
| `src/App/Order/Calculator/General/AllowedInCarrierCalculator.php`           | Remove | Redundant with capabilities                                                    |
| `src/App/Order/Calculator/General/PackageTypeCalculator.php`                | Remove | Replaced by CapabilitiesPackageTypeCalculator                                  |
| `src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php` | Remove | Capabilities already scopes options per package type                           |
| `src/App/Order/Calculator/General/TrackedCalculator.php`                    | Remove | Capabilities handles tracked per context                                       |
| `src/App/Order/Calculator/PostNl/`                                          | Remove | All 5 files                                                                    |
| `src/App/Order/Calculator/DhlForYou/`                                       | Remove | All 3 files                                                                    |
| `src/App/Order/Calculator/DhlEuroplus/`                                     | Remove | All 2 files                                                                    |
| `src/App/Order/Calculator/DhlParcelConnect/`                                | Remove | All 2 files                                                                    |
| `src/App/Order/Calculator/Gls/`                                             | Remove | All 4 files                                                                    |
| `src/App/Order/Calculator/UPSStandard/`                                     | Remove | All 3 files                                                                    |
| `src/App/Order/Calculator/UPSExpressSaver/`                                 | Remove | All 3 files                                                                    |
| `src/App/Order/Calculator/Bpost/`                                           | Remove | All 2 files                                                                    |
| `src/App/Order/Calculator/Dpd/`                                             | Remove | All 2 files                                                                    |
| `src/App/Order/Calculator/Trunkrs/`                                         | Remove | All 2 files                                                                    |
| `tests/Unit/App/Order/Calculator/CapabilitiesOptionCalculatorTest.php`      | Create | Tests for option constraint resolution                                         |
| `tests/Unit/App/Order/Calculator/CapabilitiesPackageTypeCalculatorTest.php` | Create | Tests for package type validation                                              |
| `tests/Unit/App/Order/Calculator/DeliveryDateExceptionCalculatorTest.php`   | Create | Tests for delivery date exception                                              |

---

### Task 1: Add order context method to CapabilitiesValidationService

The existing `getCapabilitiesForPackageType(cc, v2PackageType)` is optimized for checkout. Order export needs a richer context: carrier, delivery type, and options.

**Files:**

- Modify: `src/Carrier/Service/CapabilitiesValidationService.php`
- Create: `tests/Unit/Carrier/Service/CapabilitiesValidationServiceTest.php`

- [ ] **Step 1: Write failing test**

Test that `getCapabilitiesForOrderContext` returns capabilities indexed by carrier for a full order context (carrier, cc, package type, delivery type).

- [ ] **Step 2: Run test to verify it fails**

- [ ] **Step 3: Implement `getCapabilitiesForOrderContext()`**

```php
/**
 * Fetch capabilities for a full order context — carrier, destination, package type, delivery type.
 *
 * Used during order export where the full shipment configuration is known.
 * Cached per unique parameter combination.
 *
 * @param  string      $carrier        V2 carrier name
 * @param  string      $cc             Recipient country code
 * @param  string      $v2PackageType  V2 package type
 * @param  null|string $v2DeliveryType V2 delivery type (optional)
 *
 * @return array<string, RefCapabilitiesResponseCapabilityV2>
 */
public function getCapabilitiesForOrderContext(
    string $carrier,
    string $cc,
    string $v2PackageType,
    ?string $v2DeliveryType = null
): array {
    $args = [
        'carrier'      => $carrier,
        'recipient'    => ['cc' => $cc],
        'package_type' => $v2PackageType,
    ];

    if ($v2DeliveryType) {
        $args['delivery_type'] = $v2DeliveryType;
    }

    $capabilities = $this->capabilitiesRepository->getCapabilities($args);

    $indexed = [];
    foreach ($capabilities as $capability) {
        $indexed[$capability->getCarrier()] = $capability;
    }

    return $indexed;
}
```

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

```
feat(capabilities): add getCapabilitiesForOrderContext to CapabilitiesValidationService
```

---

### Task 2: Create CapabilitiesOptionCalculator

The core replacement for all carrier-specific option logic. Reads `requires`/`excludes`/`isRequired` from the capabilities response and applies them to shipment options.

**Files:**

- Create: `src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php`
- Create: `tests/Unit/App/Order/Calculator/CapabilitiesOptionCalculatorTest.php`

- [ ] **Step 1: Write failing tests**

Tests should cover:

1. `isRequired` option is forced ENABLED regardless of current value
2. `requires` array: when option A is enabled and requires B, B is forced ENABLED
3. `excludes` array: when option A is enabled and excludes B, B is forced DISABLED
4. Options not present in capabilities response are forced DISABLED
5. Multiple options with cascading requires (A requires B, B requires C)
6. Contract ID from capabilities is set on order's delivery options
7. Option disabled in carrier settings (`allowX = false`) stays DISABLED even when capabilities says `isRequired`
8. Option enabled in carrier settings AND available in capabilities → allowed

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Implement CapabilitiesOptionCalculator**

The calculator:

1. Fetches capabilities for the order's full context via `CapabilitiesValidationService::getCapabilitiesForOrderContext()`
2. Reads carrier settings for the order's carrier (the `allowX` toggles)
3. Iterates registered `orderOptionDefinitions`
4. For each option:
   - // Carrier settings take precedence: if the merchant disabled this option, skip it.
   - If not allowed in carrier settings (`allowX = false`): force DISABLED, skip further checks
   - // Capabilities determine what's valid for this specific shipment context.
   - If not present in capabilities response: disable the option
   - If present and `isRequired`: force ENABLED
   - If option is ENABLED: check `requires` array, force-enable required dependencies
   - If option is ENABLED: check `excludes` array, force-disable excluded options
5. Sets contract ID from capabilities on `$order->deliveryOptions->contractId`

Key implementation details:

- `requires`/`excludes` use capabilities keys (e.g. `requiresSignature`), not PDK keys (e.g. `signature`). Use `OrderOptionDefinitionInterface::getCapabilitiesOptionsKey()` to map between them.
- The `allowX` settings key comes from `OrderOptionDefinitionInterface::getAllowSettingsKey()`. Not all definitions have an allow key — those without one are not constrained by carrier settings.

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

```
feat(order): add CapabilitiesOptionCalculator for requires/excludes resolution
```

---

### Task 3: Modify InsuranceCalculator to use capabilities instead of SchemaRepository

Replace the schema-based insurance tier resolution with capabilities data. The capabilities response for insurance includes `insuredAmount` with `min`, `max`, and `default` — each as `{ amount: int, currency: string }`.

**Files:**

- Modify: `src/App/Order/Calculator/General/InsuranceCalculator.php`
- Modify: existing insurance tests

- [ ] **Step 1: Write failing test**

Test that insurance tiers come from capabilities min/max rather than schema enum.

- [ ] **Step 2: Run test to verify it fails**

- [ ] **Step 3: Replace SchemaRepository with CapabilitiesValidationService**

Remove `SchemaRepository` dependency. The `resolveAllowedInsuranceAmounts()` method currently:

1. Tries schema enum first
2. Falls back to `CarrierSchema::getAllowedInsuranceAmounts()`

Replace with: get the insurance option from capabilities response, read `insuredAmount.min/max/default`, generate tiers from range.

The settings-driven logic (exportInsuranceFromAmount, pricePercentage, country-specific caps) stays — it operates on the resolved tiers.

- [ ] **Step 4: Run tests, fix regressions**

- [ ] **Step 5: Commit**

```
feat(order): derive insurance tiers from capabilities instead of JSON schemas

BREAKING CHANGE: InsuranceCalculator no longer depends on SchemaRepository.

Refs: INT-930
```

---

### Task 4: Create CapabilitiesPackageTypeCalculator

Validates the order's package type against capabilities and falls back to the next available type when the selected type is not supported for the given context. Also handles the `allowInternationalMailbox` merchant setting.

**Files:**

- Create: `src/App/Order/Calculator/General/CapabilitiesPackageTypeCalculator.php`
- Create: `tests/Unit/App/Order/Calculator/CapabilitiesPackageTypeCalculatorTest.php`

- [ ] **Step 1: Write failing tests**

Tests should cover:

1. Package type available in capabilities → no change
2. Package type NOT in capabilities → falls back to next available type
3. International mailbox with `allowInternationalMailbox = true` and available in capabilities → kept
4. International mailbox with `allowInternationalMailbox = false` → falls back
5. No capabilities match at all → falls back to default package type
6. Weight exceeds package type max → falls back to next available type that fits

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Implement CapabilitiesPackageTypeCalculator**

The calculator:

1. Fetches capabilities for the order context (carrier + recipient country)
2. Checks if current package type is available in the response
3. // International mailbox has a merchant toggle — capabilities determines availability,
   // but the merchant must also have enabled it in carrier settings.
   For international mailbox: also checks `CarrierSettings::allowInternationalMailbox`
4. If not available: find the next available type from capabilities, ordered by weight capacity
   (same approach as Plan 1's `getCandidatePackageTypes` fallback logic)
5. Updates `$order->deliveryOptions->packageType`

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

```
feat(order): add CapabilitiesPackageTypeCalculator for package type validation
```

---

### Task 5: Create DeliveryDateExceptionCalculator

Exception handler for carriers that don't accept delivery dates. The `deliveryDate` option is declared in the SDK type definitions but is not implemented on either the capabilities or contract definitions API side. This must remain a local definition until the API supports it.

**Files:**

- Create: `src/App/Order/Calculator/General/DeliveryDateExceptionCalculator.php`
- Create: `tests/Unit/App/Order/Calculator/DeliveryDateExceptionCalculatorTest.php`

- [ ] **Step 1: Write failing tests**

1. Carrier in the no-delivery-date list (Bpost, DPD) → date set to null
2. Carrier NOT in the list (PostNL) → date unchanged
3. Carrier NOT in the list with no date set → no change

- [ ] **Step 2: Run test to verify it fails**

- [ ] **Step 3: Implement DeliveryDateExceptionCalculator**

```php
// @TODO: replace with capabilities check once the API implements deliveryDate
private const CARRIERS_WITHOUT_DELIVERY_DATE = [
    RefCapabilitiesSharedCarrierV2::BPOST,
    RefCapabilitiesSharedCarrierV2::DPD,
];

public function calculate(): void
{
    $carrierName = $this->order->deliveryOptions->carrier->carrier;

    if (in_array($carrierName, self::CARRIERS_WITHOUT_DELIVERY_DATE, true)) {
        $this->order->deliveryOptions->date = null;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

```
feat(order): add DeliveryDateExceptionCalculator for carriers without delivery date support
```

---

### Task 6: Update calculator chain and remove old calculators

Wire the new calculators into the chain, remove the old ones, and delete all carrier-specific calculator directories.

**Files:**

- Modify: `config/pdk-business-logic.php`
- Remove: All carrier calculator directories (10 directories, ~28 files)
- Remove: `CarrierSpecificCalculator.php`, `AllowedInCarrierCalculator.php`, `PackageTypeCalculator.php`, `PackageTypeShipmentOptionsCalculator.php`, `TrackedCalculator.php`
- Modify: tests that reference removed calculators

- [ ] **Step 1: Update calculator chain in config**

```php
'orderCalculators' => factory(function () {
    return [
        CapabilitiesPackageTypeCalculator::class,
        TriStateOptionCalculator::class,
        CapabilitiesOptionCalculator::class,
        DeliveryDateExceptionCalculator::class,
        LabelDescriptionCalculator::class,
        InsuranceCalculator::class,
        WeightCalculator::class,
        CustomerInformationCalculator::class,
        CustomsDeclarationCalculator::class,
        ExcludeParcelLockersCalculator::class,
    ];
}),
```

Note: `CapabilitiesPackageTypeCalculator` runs first (determines package type before options are resolved). `CapabilitiesOptionCalculator` runs after tri-state resolution so it can enforce `requires`/`excludes` on the resolved values.

- [ ] **Step 2: Delete carrier-specific calculator directories**

```bash
rm -rf src/App/Order/Calculator/PostNl
rm -rf src/App/Order/Calculator/DhlForYou
rm -rf src/App/Order/Calculator/DhlEuroplus
rm -rf src/App/Order/Calculator/DhlParcelConnect
rm -rf src/App/Order/Calculator/Gls
rm -rf src/App/Order/Calculator/UPSStandard
rm -rf src/App/Order/Calculator/UPSExpressSaver
rm -rf src/App/Order/Calculator/Bpost
rm -rf src/App/Order/Calculator/Dpd
rm -rf src/App/Order/Calculator/Trunkrs
```

- [ ] **Step 3: Delete replaced general calculators**

```bash
rm src/App/Order/Calculator/General/CarrierSpecificCalculator.php
rm src/App/Order/Calculator/General/AllowedInCarrierCalculator.php
rm src/App/Order/Calculator/General/PackageTypeCalculator.php
rm src/App/Order/Calculator/General/PackageTypeShipmentOptionsCalculator.php
rm src/App/Order/Calculator/General/TrackedCalculator.php
```

- [ ] **Step 4: Delete AbstractCarrierOptionsCalculator if no longer used**

Check if anything still extends it. If not, remove:

```bash
rm src/App/Order/Calculator/AbstractCarrierOptionsCalculator.php
```

- [ ] **Step 5: Run full test suite, fix regressions**

Many tests will reference removed calculators or expect carrier-specific behavior. Update test assertions to verify capabilities-driven behavior instead.

- [ ] **Step 6: Update snapshots**

```bash
yarn test:unit:snapshot
```

- [ ] **Step 7: Commit**

```
feat(order)!: replace carrier-specific calculators with capabilities-driven generic calculators

Remove 10 carrier-specific calculator directories and 5 general calculators.
Option constraints now come from the capabilities API requires/excludes/isRequired.

BREAKING CHANGE: CarrierSpecificCalculator, AllowedInCarrierCalculator,
PackageTypeCalculator, PackageTypeShipmentOptionsCalculator, TrackedCalculator removed.
Calculator chain in orderCalculators config changed.
```

---

### Task 7: Verify cleanup and remove SchemaRepository if no longer used

- [ ] **Step 1: Check remaining SchemaRepository references**

```bash
grep -r "SchemaRepository" src/ --include="*.php" -l
```

If only `SchemaRepository.php` itself remains, remove it and its config/validation directory.

- [ ] **Step 2: Remove schema files if SchemaRepository is gone**

```bash
rm -rf config/schema/
rm -rf config/validation/
rm src/Validation/Repository/SchemaRepository.php
```

- [ ] **Step 3: Check remaining CarrierSchema references**

```bash
grep -r "CarrierSchema" src/ --include="*.php" -l
```

If `CarrierSchema` is only referenced by removed code, remove it too.

- [ ] **Step 4: Run full test suite**

- [ ] **Step 5: Commit**

```
chore: remove SchemaRepository, CarrierSchema, and JSON schema files

These are no longer used — all validation now comes from the capabilities API.
```

---

### Task 8: Multi-PHP verification and PR preparation

- [ ] **Step 1: Run on PHP 7.4 and 8.1+**

```bash
PHP_VERSION=7.4 docker compose run php composer update --no-interaction --no-progress && yarn run test
PHP_VERSION=8.1 docker compose run php composer update --no-interaction --no-progress && yarn run test
```

- [ ] **Step 2: Prepare PR**

Title: `feat(order)!: replace carrier-specific calculators with capabilities API`

---

## Summary of changes

| Category            | Files                                                                                                  | Change                                    |
| ------------------- | ------------------------------------------------------------------------------------------------------ | ----------------------------------------- |
| **New calculators** | `CapabilitiesOptionCalculator`, `CapabilitiesPackageTypeCalculator`, `DeliveryDateExceptionCalculator` | Capabilities-driven generic replacements  |
| **Modified**        | `InsuranceCalculator`                                                                                  | SchemaRepository → capabilities for tiers |
| **Modified**        | `CapabilitiesValidationService`                                                                        | Add `getCapabilitiesForOrderContext()`    |
| **Modified**        | `config/pdk-business-logic.php`                                                                        | Updated calculator chain                  |
| **Removed**         | 10 carrier directories (~28 files)                                                                     | Carrier-specific calculator logic         |
| **Removed**         | 5 general calculators                                                                                  | Replaced by capabilities calculators      |
| **Removed**         | `SchemaRepository`, `CarrierSchema`, `config/schema/`, `config/validation/`                            | No longer used                            |
