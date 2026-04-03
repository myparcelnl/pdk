# Shipment Options Dynamic Frontend Design (Phase 2)

**Date:** 2026-04-03
**Status:** Draft
**Prerequisite:** Phase 1 (shipment options consistency) is complete.
**Goal:** Make frontend views and delivery options service iterate registered definitions instead of hardcoding option lists, so adding a new shipment option requires zero view/service changes.

## Problem

After Phase 1, adding a new shipment option still requires manually updating three frontend locations:

1. `CarrierSettingsItemView::getShipmentOptionsSettings()` — hardcoded `if` checks per option for allow/price form fields
2. `CarrierSettingsItemView::getDefaultExportFields()` — hardcoded export toggle list
3. `ProductSettingsView::createElements()` — hardcoded export option list
4. `DeliveryOptionsService::CONFIG_CARRIER_SETTINGS_MAP` — hardcoded allow/price key mapping

## Design

### 1. CarrierSettingsItemView::getShipmentOptionsSettings()

Currently hardcodes 3 options (signature, only recipient, priority delivery). Replace with a loop over definitions:

- Iterate all registered `orderOptionDefinitions`
- Skip definitions with null `getAllowSettingsKey()` (not consumer-toggleable)
- Skip definitions where `canHaveShipmentOption($definition)` is false for the current carrier
- For each: create toggle+price pair via `createSettingWithPriceFields(getAllowSettingsKey(), getPriceSettingsKey())`
- Apply `makeReadOnlyWhenRequired()` using `getCapabilitiesOptionsKey()`
- When `getPriceSettingsKey()` is null, only create the toggle (no price field)

### 2. CarrierSettingsItemView::getDefaultExportFields()

Currently hardcodes ~11 export toggles + special insurance handling. Replace with a loop over definitions:

- Iterate all registered `orderOptionDefinitions`
- Skip definitions with null `getCarrierSettingsKey()` (no export setting)
- Skip definitions where `canHaveShipmentOption($definition)` is false for the current carrier
- **Insurance special case:** Use `instanceof InsuranceDefinition` to render the custom insurance fields (SELECT dropdowns with amounts, sub-fields for thresholds) instead of the generic tri-state toggle. Keep UI concerns in the view, not the definition.
- For all other definitions: create `INPUT_TRI_STATE` element with `makeReadOnlyWhenRequired()` using `getCapabilitiesOptionsKey()`
- Existing inter-option dependencies (e.g. age check forcing signature/only recipient read-only) stay as explicit view-level logic after the generic loop

### 3. ProductSettingsView::createElements()

Currently hardcodes 8 export option tri-state toggles. Replace with a loop over definitions:

- Iterate all registered `orderOptionDefinitions`
- Only include definitions with both `getProductSettingsKey()` and `getShipmentOptionsKey()` non-null (filters out product-only settings like countryOfOrigin, customsCode, packageType)
- For each: create `InteractiveElement($productKey, Components::INPUT_TRI_STATE)`
- No capability checks or `makeReadOnlyWhenRequired` — product settings are global, not carrier-specific
- Product-only definitions stay in their dedicated sections unchanged

### 4. DeliveryOptionsService

The `CONFIG_CARRIER_SETTINGS_MAP` constant is renamed and split:

**Static constant** — renamed to `NON_DEFINITION_CARRIER_SETTINGS_MAP` with a comment explaining it only covers non-shipment-option entries (delivery types, package types, and other settings not covered by definitions):

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

**Dynamic method** — builds the complete map by merging definition-derived allow/price keys with the static entries:

```php
private static function getCarrierSettingsMap(): array
{
    $definitions = Pdk::get('orderOptionDefinitions');
    $map = [];

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

All references to `self::CONFIG_CARRIER_SETTINGS_MAP` are replaced with `self::getCarrierSettingsMap()`.

### 5. Test Updates

- The existing `FrontendDefinitionConsistencyTest` can be strengthened: instead of checking that unmapped entries are delivery-type-like, assert that zero shipment option entries remain in the static map.
- Update any snapshot tests affected by ordering changes from dynamic iteration.
- Existing flow and consistency tests from Phase 1 continue to pass unchanged.

## Files Affected

| File                                                           | Change                                                                            |
| -------------------------------------------------------------- | --------------------------------------------------------------------------------- |
| `src/Frontend/View/CarrierSettingsItemView.php`                | `getShipmentOptionsSettings()` and `getDefaultExportFields()` iterate definitions |
| `src/Frontend/View/ProductSettingsView.php`                    | Export options section iterates definitions                                       |
| `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`   | Rename constant, add dynamic method, replace references                           |
| `tests/Unit/App/Options/FrontendDefinitionConsistencyTest.php` | Strengthen assertions                                                             |
| `tests/__snapshots__/`                                         | Update ordering changes                                                           |

## Out of Scope

- Delivery type definitions (separate concern, different patterns)
- Insurance UI customization at the definition level (stays as view-level `instanceof` check)
- Product settings carrier-awareness (product settings are global)
- Inter-option UI dependencies (stay as explicit view logic)
