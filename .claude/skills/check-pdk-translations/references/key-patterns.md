# PDK translation key patterns

Reference for what generates each translation key the skill audits. Read this when extending `find_missing_keys.sh` or when the user asks about a category beyond the defaults.

## shipment*options*\*

**Pattern:** `shipment_options_<snake_case_capability_key>` (label, audited by the script). `_description` and `_subtext` siblings exist at runtime but are not auto-emitted (see "Description and subtext are optional" below).

**Source of truth:** PDK `*Definition` classes in `src/App/Options/Definition/`. Each definition's `getCapabilitiesOptionsKey()` returns the camelCase capability key from `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()`. The form (`createShipmentOptionField.ts` in JS-PDK) iterates `Object.keys(carrier.options)` (those camelCase keys) and feeds each through `getFieldLabel(name) = snakeCase('shipmentOptions_' + name)`.

**Why Definitions rather than just SDK:** Definitions are PDK's source of truth for which options are exposed. They also expose `getShipmentOptionsKey()`, which is the _legacy_ PDK key — useful for finding a reusable existing translation when an option was renamed (`signature` → `requires_signature`, `large_format` → `oversized_package`).

**Description and subtext are optional.** `defineFormField` in `js-pdk/apps/admin/src/forms/helpers/defineFormField.ts` auto-attaches `description` and `subtext` translation lookups to every shipment-option field. For each label `shipment_options_X`, the form will also try `shipment_options_X_description` and `shipment_options_X_subtext`, but missing keys silently fall through (key returned, nothing rendered). The script therefore only flags missing **labels** — auto-flagging every description sibling produced false-positive noise during the skill's first iteration. The translate-mode workflow opts in to descriptions per category instead.

## settings*carrier*\* (per-option)

**Pattern:** `settings_carrier_<snake_case>` (base key, audited by the script). `_description` may exist at runtime but is not auto-emitted.

**Source:** PDK Definition classes via three methods on `AbstractOrderOptionDefinition`:

- `getAllowSettingsKey()` — defaults to `'allow' . ucfirst(getShipmentOptionsKey())`, e.g. `'allowSaturdayDelivery'` → `settings_carrier_allow_saturday_delivery`.
- `getPriceSettingsKey()` — defaults to `'price' . ucfirst(getShipmentOptionsKey())`, e.g. `'priceSaturdayDelivery'` → `settings_carrier_price_saturday_delivery`.
- `getCarrierSettingsKey()` — defaults to `'export' . ucfirst(getShipmentOptionsKey())`, e.g. `'exportSignature'` → `settings_carrier_export_signature`. Definitions may override any of these to return `null` or a custom value.

`introspect_keys.php` invokes each method via PHP reflection, so inherited defaults from the abstract class are resolved natively. It emits the three base keys whenever the method returns non-null. Explicit `null` overrides correctly suppress the key (no false positives for definitions like `ExcludeParcelLockersDefinition`/`SaturdayDeliveryDefinition` that return `null` from one or more of these methods).

## `settings_product_*` (per-option)

**Pattern:** `settings_product_<snake_case>` (base key, audited by the script). `_description` may exist at runtime but is not auto-emitted.

**Source:** Definition `getProductSettingsKey()` — e.g. `'fitInDigitalStamp'` → `settings_product_fit_in_digital_stamp`. The default in the abstract class delegates to `getCarrierSettingsKey()`, so most options yield a `settings_product_export_*` key via that delegation. PHP reflection invokes the method directly, so:

- explicit non-null overrides yield their own key (e.g. `FitInDigitalStampDefinition` → `settings_product_fit_in_digital_stamp`),
- explicit `null` overrides suppress the key,
- unoverridden methods inherit the abstract delegate (typical for shipment-option definitions like `SignatureDefinition` → `settings_product_export_signature`).

## `settings_carrier_*` (static dividers)

**Pattern:** `settings_carrier_<divider_name>_title` (visible header). `_description` is optional and not auto-flagged.

**Source:** `createGenericLabel('X')` calls inside `src/Frontend/View/CarrierSettingsItemView.php`. The view's `getLabelPrefix()` returns `'carrier'` and `KEY_PREFIX` is `'settings'`, so each call produces a base of `settings_carrier_<X>`. The renderer then looks up `<base>_title` (and optionally `<base>_description`). The bare key is not used directly. Examples grep-found: `export`, `export_returns`, `delivery_options`, `delivery_options_delivery`, `delivery_options_pickup`, `delivery_moments`, `shipment_options`.

Adding a new divider in PHP means the next run picks it up automatically; renaming or moving the view requires updating the script.

## delivery*type*\*

**Pattern:** `delivery_type_<lowercase_name>`.

**Source:** SDK `ShipmentDefsDeliveryOptionsDeliveryNameV2` in `vendor/myparcelnl/sdk/src/Client/Generated/CoreApi/Model/`. Constants are already lowercase string values (`'morning'`, `'standard'`, etc.). `getDynamicTranslation('delivery_type', name)` in `js-pdk/apps/admin/src/utils/translations/getDynamicTranslation.ts` does `${prefix}_${input.toLowerCase()}`.

**Note on enums:** there are several delivery-type-shaped classes in the SDK (`RefTypesDeliveryTypeV2`, `RefShipmentOptionsDeliveryTypeAll`, etc.). `ShipmentDefsDeliveryOptionsDeliveryNameV2` is the one whose values match the runtime translation keys. If a future SDK regen moves these constants, update the script.

## `package_type_*`

**Pattern:** `package_type_<lowercase_name>`.

**Source:** SDK `RefShipmentPackageTypeV2`. Constants are UPPER_SNAKE values like `'PACKAGE'`, `'MAILBOX'`. `getPackageTypeTranslation` calls `getDynamicTranslation('package_type', name)`, which lowercases. Result: `package_type_package`, `package_type_mailbox`, etc.

## carrier\_\*

**Pattern:** `carrier_<lowercase_carrier_name>` (with original underscores preserved, e.g. `carrier_dhl_for_you`).

**Source:** SDK `RefCapabilitiesSharedCarrierV2`. Constants like `DHL_FOR_YOU = 'DHL_FOR_YOU'`. `createCarrierField.ts` falls back to `translate('carrier_' + snakeCase(carrier.carrier))` when the API doesn't return a `human` label. The translation is therefore a fallback — useful when a carrier is added before its translation row exists.

## What the skill does NOT cover

- **Hardcoded translation keys** scattered through PHP (`Language::translate('return_shipment_created_title')` etc.) — too many ad-hoc keys to enumerate reliably without static analysis. Add to the script if a stable pattern emerges.
- **Plugin-specific translation keys** (e.g. PrestaShop or WooCommerce admin labels) — those are owned by the plugin, not the PDK.
- **JS-PDK app-only keys** that don't trace back to PDK definitions or SDK constants. The user mostly cares about the form fields the PDK drives, which is what this skill targets.

## Why English-first translation matters

When drafting translations for new keys, get English right before generating the other four languages. The Dutch/French/German/Italian drafts will be derived from the English source. A mistranslation at the English step propagates to four languages and is usually caught only when someone notices an odd label in production. The "verify uncertain English" step in the skill workflow exists specifically to avoid this.
