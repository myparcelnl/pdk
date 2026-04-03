# Shipment Options Consistency Design

**Date:** 2026-04-03
**Status:** Draft
**Goal:** Reduce the number of places a developer must touch when adding a new shipment option from 9+ files to 2-3 files, with all other keys and registrations derived automatically.

## Problem

Adding a new shipment option currently requires changes in 9+ files:

1. `ShipmentOptions` — constant, attribute, cast, `ALL_SHIPMENT_OPTIONS` array
2. `CarrierSettings` — `EXPORT_*`, `ALLOW_*`, `PRICE_*` constants + defaults
3. `ProductSettings` — `EXPORT_*` constant + default
4. New Definition class in `App/Options/Definition/`
5. `pdk-business-logic.php` — register definition
6. `CarrierSchema` — new `canHave*()` method
7. `pdk-settings.php` — default values
8. `Fulfilment/ShipmentOptions` — if applicable
9. Test datasets

Keys are duplicated across models with inconsistent naming conventions (some use `export*`, some `allow*`), and there is no enforcement that all these locations stay in sync.

## Design

### 1. Abstract Definition Base Class

A new `AbstractOrderOptionDefinition` provides convention-based defaults. Only two methods are required — the rest are derived:

```php
abstract class AbstractOrderOptionDefinition implements OrderOptionDefinitionInterface
{
    /**
     * The internal PDK key used on the ShipmentOptions model (e.g. 'signature', 'ageCheck').
     * This is the root key from which carrier/product/allow/price settings keys are derived.
     * These keys correspond to the legacy API naming used by the shipment-, order v1,
     * delivery-options and other legacy API endpoints.
     *
     * Return null if this definition does not represent a shipment option (e.g. product-only
     * settings like CountryOfOrigin). When null, all derived settings keys also return null
     * automatically, and the option will not appear on the ShipmentOptions model.
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

**Key design decisions:**

- New options get **full coverage by default** (export, allow, price settings). To opt out, override the method and return `null`.
- `getShipmentOptionsKey()` is the root key from which settings keys are derived.
- `getCapabilitiesOptionsKey()` is the explicit bridge to SDK types (since SDK names diverge from PDK names, e.g. `requiresAgeVerification` vs `ageCheck`).
- PDK shipment option keys (`return`, `ageCheck`, `largeFormat`) are **not** migrated to SDK names — they are the public contract consumed by platform integrations.
- `ShipmentOptions` constants (e.g. `AGE_CHECK`, `SIGNATURE`) are kept for backwards compatibility with platform integrations but marked `@deprecated`. All internal PDK usage of these constants must be replaced with references to the definition's `getShipmentOptionsKey()` so the PDK itself has zero remaining usage.

**Opt-out examples:**

- `SameDayDeliveryDefinition` — no export/product setting: override `getCarrierSettingsKey()` and `getProductSettingsKey()` → `null`
- `CountryOfOriginDefinition` — no shipment option: override `getShipmentOptionsKey()` → `null`; `getAllowSettingsKey()`, `getPriceSettingsKey()` → `null`
- `ExcludeParcelLockersDefinition` — no price surcharge: override `getPriceSettingsKey()` → `null`

**Explicit overrides for convention exceptions:**

- `DirectReturnDefinition` — the constant is `DIRECT_RETURN` but the value is `return`, so the derived carrier settings key `exportReturn` is actually correct. No override needed.
- `PriorityDeliveryDefinition` — currently uses `allowPriorityDelivery` as carrier settings key instead of `exportPriorityDelivery`. This needs an explicit override for `getCarrierSettingsKey()`. Consider whether this should be normalized to `exportPriorityDelivery` as a breaking change.

### 2. Dynamic Settings Attribute Registration via Trait

A `ResolvesOptionAttributes` trait provides a reusable method for models to build their attributes from definitions:

```php
trait ResolvesOptionAttributes
{
    /**
     * @param callable(OrderOptionDefinitionInterface): ?string $keyExtractor
     * @param mixed $default
     * @return array
     */
    protected function resolveOptionAttributes(callable $keyExtractor, $default): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $attributes = [];

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

**Usage per model:**

- `CarrierSettings` — calls `resolveOptionAttributes()` three times (export → `TriStateService::INHERIT`, allow → `false`, price → `0`) and merges with its static non-option attributes.
- `ProductSettings` — calls once with `getProductSettingsKey()` → `TriStateService::INHERIT`.
- `ShipmentOptions` — calls once with `getShipmentOptionsKey()` → `TriStateService::INHERIT`.

Static non-option attributes (`CUTOFF_TIME`, `DROP_OFF_DELAY`, etc.) remain hardcoded on the model as they are today.

### 3. IDE Helper Integration

The project already has a `generate:ide-helper` command that:

- Runs automatically on `composer install/update` via `post-autoload-dump`
- Uses reflection via `PhpSourceParser` to introspect model attributes
- Generates `@property` docblocks in `.meta/pdk_ide_helper.php`

**Integration approach:**

- The `ResolvesOptionAttributes` trait populates attributes at construction time, so reflection-based introspection should pick them up automatically.
- **Verification step:** During implementation, confirm that `PhpSourceParser` instantiates models (rather than reading the static `$attributes` property). If it only reads static definitions, adjust the parser to instantiate the model or have the trait merge attributes in the property initializer.

### 4. CarrierSchema Simplification

`CarrierSchema` currently has ~15 individual `canHave*()` methods that all delegate to `canHaveShipmentOption($definition)`. With the abstract class providing a default `validate()`, these become redundant.

**Approach:**

- Remove all individual `canHave*()` method bodies from the class.
- Keep `canHaveShipmentOption($definition)` as the single real public API.
- Add a `__call()` magic method that proxies legacy calls for backwards compatibility:
  - Strips the `canHave` prefix from the method name (e.g. `canHaveSignature` → `Signature`)
  - Resolves the matching Definition class (e.g. `SignatureDefinition`)
  - Delegates to `canHaveShipmentOption($definitionClass)`
  - Throws `BadMethodCallException` for unknown methods
- Add `@method` docblock annotations marked `@deprecated` so IDEs and PHPStan still recognize the legacy methods while signaling they should not be used.
- The `@method` annotations can be generated by the existing ide-helper command, since it already knows all registered definitions.

```php
/**
 * @deprecated This class will be replaced with generic capabilities-focussed functionality.
 *
 * @deprecated @method bool canHaveSignature()
 * @deprecated @method bool canHaveAgeCheck()
 * ...
 */
class CarrierSchema implements DeliveryOptionsValidatorInterface
{
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

        throw new BadMethodCallException("Method {$name} does not exist");
    }
}
```

This removes ~15 boilerplate methods from the codebase while preserving full backwards compatibility for external consumers.

### 5. Interface Changes

The `OrderOptionDefinitionInterface` gains two new methods:

```php
interface OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string;
    public function getProductSettingsKey(): ?string;
    public function getShipmentOptionsKey(): ?string;
    public function getCapabilitiesOptionsKey(): ?string;
    public function getAllowSettingsKey(): ?string;    // NEW
    public function getPriceSettingsKey(): ?string;    // NEW
    public function validate(CarrierSchema $carrierSchema): bool;
}
```

### 6. Fulfilment ShipmentOptions Integration

`Fulfilment\ShipmentOptions` is a separate model that uses boolean values (not tri-state) and includes additional non-option fields (`deliveryDate`, `deliveryType`, `packageType`, `cooledDelivery`, `labelDescription`). Its option attributes are currently hardcoded — adding a new shipment option requires manually adding it here too.

**Approach:**

- Use the `ResolvesOptionAttributes` trait to dynamically build option attributes from definitions, using `null` as default and `bool` as cast.
- Non-option attributes remain static on the model.
- `fromPdkDeliveryOptions()` continues to work as-is since it reads from the resolved PDK `ShipmentOptions` model which already uses definitions.

### 7. API Request Encoding

The definitions should drive the entire flow from PDK settings to API export. Three export paths exist:

**Legacy API** (`PostShipmentsRequest::getOptions()`):

- Uses `$shipmentOptions->toSnakeCaseArray()` to convert all model attributes to snake_case.
- Since `ShipmentOptions` dynamically registers attributes from definitions, new options are automatically included in the legacy API output. No code change needed.

**Fulfilment API** (`PostOrdersRequest::getShipmentOptions()`):

- Uses `$shipment->options->toArray()` on the `Fulfilment\ShipmentOptions` model.
- Same principle — dynamic attributes from the trait flow through automatically.

**V2/Capabilities API** (`ShipmentOptions::toCapabilitiesDefinitions()`):

- Already iterates all registered definitions and maps via `getCapabilitiesOptionsKey()`. Already covered by this design.

**Key insight:** Because all three export paths serialize model attributes generically (via `toSnakeCaseArray()` or `toArray()`), dynamically registered attributes are included automatically. No request classes need modification when adding a new shipment option.

### 7b. Carrier Capability Options Filtering

The Carrier model's `$options` property contains all options from the capabilities API response, including options that may not have a registered `OrderOptionDefinition` in the PDK. The frontend reads this property to render settings UI. If an option exists in the API response but has no PDK definition, the frontend would render it but the PDK wouldn't know how to calculate, store, or export it.

**Requirement:** The Carrier's options must be filtered so that only options with a matching registered definition (by `getCapabilitiesOptionsKey()`) are exposed to consumers. Unregistered options are stripped before reaching the frontend or any other consumer. This ensures that the definitions are the single gatekeeper for which options are available in the system.

### 8. Verification and Test Coverage

**End-to-end flow test:**

A test registers a fake/test shipment option definition (e.g. `TestOptionDefinition`) and verifies the entire pipeline:

1. **Settings registration** — The option's `export*`, `allow*`, and `price*` keys exist as attributes on `CarrierSettings` and `ProductSettings` with correct defaults.
2. **ShipmentOptions model** — The option key is a valid attribute on `ShipmentOptions` with tri-state default.
3. **Fulfilment model** — The option key is a valid attribute on `Fulfilment\ShipmentOptions` with boolean cast.
4. **Option calculation** — `PdkOrderOptionsService::calculateShipmentOptions()` resolves the option value through the carrier settings → product settings → shipment options priority chain.
5. **Carrier validation** — `CarrierSchema::canHaveShipmentOption()` correctly reports availability based on capabilities data.
6. **Legacy API export** — `PostShipmentsRequest` includes the option in snake_case in the request body.
7. **Fulfilment API export** — `PostOrdersRequest` includes the option in the encoded output.
8. **V2 API export** — `ShipmentOptions::toCapabilitiesDefinitions()` maps the option to its capabilities key.
9. **V2 API import** — `ShipmentOptions::fromCapabilitiesDefinitions()` maps the capabilities key back to the shipment options key.

This single test proves that adding a Definition class + registering it is sufficient for the option to work across the entire system.

**IDE helper verification:**

A test that asserts dynamic attributes from definitions are correctly picked up by the `generate:ide-helper` command. This confirms that the `PhpSourceParser` can introspect dynamically registered attributes and that IDE autocomplete stays accurate.

**Consistency tests:**

- Every registered definition's `getShipmentOptionsKey()` matches a deprecated constant on `ShipmentOptions`.
- Every derived settings key (carrier, product, allow, price) that is non-null is present as an attribute on the corresponding model.
- No orphaned constants exist on settings models that aren't backed by a definition.
- No internal PDK code references `ShipmentOptions::*` constants directly — all internal usage goes through definitions. This can be enforced via a grep-based test or static analysis rule.

### 9. Claude Skill for Adding Shipment Options

A Claude Code skill is created that guides developers step-by-step through adding a new shipment option. The skill:

- Asks for the PDK shipment option key and SDK capabilities key
- Generates the Definition class with appropriate overrides
- Registers it in `pdk-business-logic.php`
- Adds a deprecated constant to `ShipmentOptions` (for backwards compat with platform integrations only)
- Runs the ide-helper generator
- Runs tests to verify consistency

## After: Adding a New Shipment Option (2-3 files)

1. **Create Definition class** extending `AbstractOrderOptionDefinition` — implement `getShipmentOptionsKey()` and `getCapabilitiesOptionsKey()`, override any opt-outs.
2. **Register in `pdk-business-logic.php`** — add one line.
3. **Add deprecated constant to `ShipmentOptions`** — for backwards compatibility with platform integrations only; the PDK itself must not use it.

Everything else (carrier settings, product settings, allow/price keys, validation, ide-helper docblocks) is derived automatically.

## Files Affected

| File                                                           | Change                                                        |
| -------------------------------------------------------------- | ------------------------------------------------------------- |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`  | Add `getAllowSettingsKey()`, `getPriceSettingsKey()`          |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php` | **New** — abstract base with convention defaults              |
| `src/App/Options/Definition/*Definition.php`                   | Refactor to extend abstract, remove boilerplate               |
| `src/Base/Concern/ResolvesOptionAttributes.php`                | **New** — trait for dynamic attribute building                |
| `src/Settings/Model/CarrierSettings.php`                       | Use trait, remove option-derived constants/attributes         |
| `src/Settings/Model/ProductSettings.php`                       | Use trait, remove option-derived constants/attributes         |
| `src/Shipment/Model/ShipmentOptions.php`                       | Use trait, deprecate constants, remove internal usage         |
| `src/Fulfilment/Model/ShipmentOptions.php`                     | Use trait, remove hardcoded option attributes                 |
| `src/Validation/Validator/CarrierSchema.php`                   | Replace `canHave*()` methods with `__call()` proxy            |
| `private/Types/Php/IdeHelperGenerator.php`                     | Verify dynamic attributes are picked up (adjust if needed)    |
| `tests/`                                                       | End-to-end flow test, consistency tests, ide-helper assertion |
| `.claude/skills/add-shipment-option.md`                        | **New** — Claude skill for guided option creation             |

## Phase 2: Dynamic Frontend Views (Future)

The following frontend classes currently use hardcoded option lists, meaning adding a new shipment option still requires modifying them:

- **`CarrierSettingsItemView::getShipmentOptionsSettings()`** — hardcoded `if ($this->carrierSchema->canHaveX())` checks per option, each creating allow/price form field pairs
- **`ProductSettingsView::createElements()`** — hardcoded list of `InteractiveElement` instances for each export option
- **`DeliveryOptionsService::CONFIG_CARRIER_SETTINGS_MAP`** — hardcoded mapping of allow/price keys for frontend delivery options data

**Goal:** These should iterate over registered definitions instead, so that adding a definition automatically adds the corresponding form fields and frontend data. Each definition already provides the allow, price, export, and capabilities keys needed to build the form elements.

**Approach (high-level):**

- `CarrierSettingsItemView::getShipmentOptionsSettings()` iterates definitions, checks `canHaveShipmentOption($definition)`, and uses `getAllowSettingsKey()` + `getPriceSettingsKey()` to create form fields dynamically. The `makeReadOnlyWhenRequired()` call uses `getCapabilitiesOptionsKey()`.
- `ProductSettingsView::createElements()` iterates definitions and creates `InteractiveElement` instances for each non-null `getProductSettingsKey()`.
- `DeliveryOptionsService::CONFIG_CARRIER_SETTINGS_MAP` is built dynamically from definitions' `getAllowSettingsKey()` and `getPriceSettingsKey()`.

**This phase is not part of the current implementation plan** but should follow directly after phase 1 is complete and verified.

## Verification: Frontend Uses Definitions (Current Phase)

As part of the current phase, add a consistency test that asserts:

- Every option rendered in `CarrierSettingsItemView::getShipmentOptionsSettings()` corresponds to a registered definition.
- Every option in `ProductSettingsView::createElements()` corresponds to a registered definition.
- Every entry in `DeliveryOptionsService::CONFIG_CARRIER_SETTINGS_MAP` corresponds to a registered definition's allow/price keys.

This test will fail if someone adds a hardcoded option without a definition, and will serve as the forcing function for the phase 2 migration.

## Out of Scope

- Migrating PDK shipment option keys to match SDK names (backwards-incompatible)
- Removing deprecated `CarrierSchema::canHave*()` method signatures (deprecate via `@method` docblock only)
