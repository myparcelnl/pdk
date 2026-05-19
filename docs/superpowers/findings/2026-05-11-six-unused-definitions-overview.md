# Six unused OptionDefinition classes — per-item overview

Companion to `2026-05-11-v4-capabilities-cleanup-findings.md` §Schema A-1..A-6.

## Common pattern

All six classes share the same characteristics:

- Live in `src/App/Options/Definition/` (production namespace).
- Extend `AbstractOrderOptionDefinition`.
- **NOT** registered in `config/pdk-business-logic.php` (`orderOptionDefinitions` array).
- Referenced only by test files (factories, datasets, snapshots).
- Have **zero** references from PDK production code outside their own directory (verified by `rg -l '<ClassName>' src/ --type=php --glob '!src/App/Options/Definition/*'`).
- Where the underlying concept IS still in use (Schema A-3, A-5, A-6), the usage runs through hand-declared property keys on `ProductSettings`, not through the Definition class.

Verdict: all six classes are safe to delete. The hand-declared property paths continue to work; tests need their references rewritten to mirror how production code already uses the same concepts.

## Per-item analysis

### 1. `CountryOfOriginDefinition` (Schema A-1)

- **Concept:** Country-of-origin shipment option (typically used for customs declarations).
- **Currently exported as:** `getShipmentOptionsKey()` returns null, `getCapabilitiesOptionsKey()` returns null — inert.
- **Hand-declared elsewhere?** Customs-related concepts live in `CustomsDeclarationItem` (`countryOfOrigin` property), not via this Definition.
- **Recommended fate:** Delete. Migrate test references to the customs-declaration property paths used by production.

### 2. `CustomsCodeDefinition` (Schema A-2)

- **Concept:** Customs HS code per item.
- **Currently exported as:** both getter methods return null.
- **Hand-declared elsewhere?** `CustomsDeclarationItem::classification` (HS code) carries this concept in production.
- **Recommended fate:** Delete. Tests redirect to `CustomsDeclarationItem::classification`.

### 3. `DisableDeliveryOptionsDefinition` (Schema A-3)

- **Concept:** Product-level flag to disable the delivery-options widget per product.
- **Currently exported as:** `getProductSettingsKey() = 'disableDeliveryOptions'` (only key set).
- **Hand-declared elsewhere?** `ProductSettings::DISABLE_DELIVERY_OPTIONS = 'disableDeliveryOptions'` and the corresponding `@property` are hand-declared; the `ResolvesOptionAttributes` trait would have auto-registered the same key if this Definition were in the config, but the hand-declared path already covers it.
- **Recommended fate:** Delete. Tests redirect to `ProductSettings::DISABLE_DELIVERY_OPTIONS` (or the property directly).

### 4. `FitInDigitalStampDefinition` (Schema A-4)

- **Concept:** Whether a product fits inside a digital-stamp shipment.
- **Currently exported as:** both getter methods return null — test fixture only.
- **Hand-declared elsewhere?** Digital-stamp logic is in `WeightCalculator`/`CapabilitiesValidationService` (weight tiers + capabilities) — no property on `ProductSettings`.
- **Recommended fate:** Delete. Tests don't need a replacement reference — they should test the `WeightCalculator` digital-stamp path directly.

### 5. `FitInMailboxDefinition` (Schema A-5)

- **Concept:** Whether a product fits in a mailbox shipment — product-only setting that influences package-type choice via the cart calculator (not exported in any shipment-options payload).
- **Currently exported as:** both getter methods return null. The Definition is conceptually correct (product-only, no exported keys) but the class itself is unreferenced.
- **Hand-declared elsewhere?** `ProductSettings::FIT_IN_MAILBOX = 'fitInMailbox'` + `@property int $fitInMailbox`. `CartCalculationService` reads `$line->product->mergedSettings->fitInMailbox` directly.
- **Recommended fate:** Delete the Definition class. The product setting and its consumer remain untouched.

### 6. `PackageTypeDefinition` (Schema A-6)

- **Concept:** Product-level default package type.
- **Currently exported as:** `getProductSettingsKey() = 'packageType'`, no shipment/capabilities key.
- **Hand-declared elsewhere?** `ProductSettings::PACKAGE_TYPE` hand-declared. Package type also resolved per-shipment by `CapabilitiesPackageTypeCalculator` (independent path).
- **Recommended fate:** Delete. `ProductSettings::PACKAGE_TYPE` continues to carry the product-level default.

## Cleanup execution notes

1. Delete order does not matter — none of the 6 depend on each other.
2. Each deletion removes ~1-3 test files (factory + dataset reference).
3. After deletion, the `orderOptionDefinitions` registry is unchanged (these were never registered).
4. No plugin code is affected (verified — 0 plugin refs).
5. PHPStan should be re-run after the 6 deletions; expect no new errors.

## Why this is worth doing

- **Reduces schema surface area** from 25 Definitions to 19, sharpening the answer to "what is an option in PDK".
- **Eliminates ambiguity** about whether a Definition has to exist for a setting to work (it doesn't — these 6 prove the setting works without a Definition).
- **Strengthens the case for the `validate()` removal plan**: with the dead Definitions gone, the remaining 19 will more clearly show that `validate()` is unused except by `ExcludeParcelLockersCalculator`.
