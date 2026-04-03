---
name: add-shipment-option
description: Guide step-by-step through adding a new shipment option to the PDK
---

# Add Shipment Option

Use this skill when adding a new shipment option to the PDK.

## Information Gathering

Ask the user the following questions one at a time:

### 1. SDK Keys

Ask: "What is the snake_case key from `RefShipmentShipmentOptions::attributeMap()`? (e.g. `my_new_option`)"

Then: "What is the snake_case key from `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()`? (e.g. `my_new_option`)"

If the option is not in the SDK types, suggest regenerating the OpenAPI generated types and/or updating the SDK first. New shipment options should be defined in the API spec and reflected in the SDK before being added to the PDK. Only use plain string values as a last resort for options that are PDK-internal and not part of the API (e.g. `excludeParcelLockers`).

### 2. Allow Setting

Ask: "Should consumers be able to toggle this option at checkout in the delivery options widget? (allow setting)"

Explain: The `allow*` setting controls whether the consumer can choose this option at checkout. Only use for options where consumer choice is appropriate (e.g. signature, pickup). Options that should always be applied by the merchant via the export setting and should not be (de)selectable by the consumer (e.g. age check for alcohol, insurance, hide sender) should NOT have an allow setting.

If yes: the default `getAllowSettingsKey()` will produce `allow{OptionName}`.
If no: override `getAllowSettingsKey()` to return `null`.

### 3. Price Setting

Ask: "Should there be a price surcharge setting for this option? (price setting)"

Explain: The `price*` setting represents a monetary surcharge added to the shipping cost when the option is active. This is typically used together with an allow setting so the consumer can see the surcharge at checkout, but can also be used for merchant-applied options where the cost is passed on.

If yes: the default `getPriceSettingsKey()` will produce `price{OptionName}`.
If no: override `getPriceSettingsKey()` to return `null`.

### 4. Value Type

Ask: "Is this a standard boolean/tri-state option, or does it have a numeric value (like insurance amount)?"

If tri-state: no override needed (default `getShipmentOptionsCast()` returns `TriStateService::TYPE_STRICT`).
If numeric: override `getShipmentOptionsCast()` to return `'int'`.

### 5. Carrier/Product Settings

Ask: "Should this option have carrier-level and product-level export settings?"

Most options have both. Some (like same day delivery) have neither — they are controlled purely by capabilities. Override `getCarrierSettingsKey()` and/or `getProductSettingsKey()` to return `null` to opt out.

## Implementation Steps

After gathering the information:

### 1. Create the Definition Class

Create `src/App/Options/Definition/{OptionName}Definition.php` extending `AbstractOrderOptionDefinition`.

Required methods:

- `getShipmentOptionsKey()` — return `Str::camel(RefShipmentShipmentOptions::attributeMap()['sdk_key'])`
- `getCapabilitiesOptionsKey()` — return `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['capabilities_key']`

Add null overrides for any settings the user opted out of.

### 2. Register the Definition

Add the new definition to the `orderOptionDefinitions` array in `config/pdk-business-logic.php`. Add the import at the top of the file.

### 3. Add Deprecated Constant to ShipmentOptions

Add a `@deprecated` constant to `src/Shipment/Model/ShipmentOptions.php` for backwards compatibility with platform integrations. Do NOT use this constant anywhere in PDK code — use the definition's `getShipmentOptionsKey()` instead.

### 4. Run IDE Helper

```bash
docker compose run php composer console generate:ide-helper
```

### 5. Run Tests

```bash
yarn run test:unit
```

The consistency tests will verify everything is wired up correctly. Update snapshots if needed:

```bash
yarn test:unit:snapshot
```

## Notes

- The `Carrier` model filters serialized options to only include those with registered definitions. Adding a definition automatically makes the option visible to the frontend.
- `CarrierSettings`, `ProductSettings`, `ShipmentOptions`, and `Fulfilment\ShipmentOptions` all build their option attributes dynamically from definitions — no manual attribute registration needed.
- `CarrierSchema::canHaveShipmentOption()` checks capabilities for the option automatically via the definition's capabilities key.
