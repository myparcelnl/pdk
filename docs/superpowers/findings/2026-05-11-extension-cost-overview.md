# Extension cost overview — adding new carriers, types, and options

Companion to `2026-05-11-v4-capabilities-cleanup-findings.md`. Consolidates the per-pattern §C-4 ("Extension cost") observations into a single architectural reference.

Each section answers the same questions: **what files do you touch, and what's the friction?** Answers reflect **current** state (HEAD `8752adb5`) AND the **post-cleanup** state we'd reach if all recommended plans land.

---

## Adding a new Carrier

### Current (HEAD `8752adb5`)

1. **SDK** — add to `RefCapabilitiesSharedCarrierV2` enum + contract definitions response. (Outside PDK; owned by SDK team.)
2. **PDK** — add an entry to `Carrier::CARRIER_NAME_ID_MAP` (legacy ID mapping for V1-export-eligible carriers). Add a `CARRIER_<NAME>_LEGACY_NAME` const if applicable.
3. **Server-side** — adds the carrier to its contract definitions API response.
4. **PDK version bump**, plugins redeploy.

**Touch points: 1 PDK file (`Carrier.php`) + SDK + server.** Most of the work is upstream.

### Post-cleanup (after INT-1441)

1. SDK adds it.
2. Server-side adds it.
3. PDK consumes via the centralized SDK mapping — **0 PDK code changes**.

**Touch points: 0 PDK files** for a standard carrier. (Special-rule carriers like DPD's customer-info or BPost's delivery-date exception still need a scoped calculator file — see "Special-rule carriers" below.)

---

## Adding a new PackageType

### Current

1. **SDK** — `RefShipmentPackageTypeV2` enum entry.
2. **PDK** — `DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP`, `PACKAGE_TYPES_V2_MAP`, `PACKAGE_TYPES_NAMES` array, `PACKAGE_TYPES_IDS` array, possibly other constant maps in `DeliveryOptions`. (~5 touch points all in `DeliveryOptions.php`.)
3. **Capabilities API** — emits the new type in carrier capability responses.
4. **Calculators** — `CapabilitiesPackageTypeCalculator` and `WeightService::getEmptyWeightForPackageType()` accept new strings; international-mailbox rule may need a tweak if applicable.

**Touch points: ~5 in `DeliveryOptions.php` + SDK + API.**

### Post-cleanup

After Shipment B-1 (drop `PackageType` model wrapper) + INT-1441 (dynamic SDK mapping):

1. SDK adds it.
2. API emits it.
3. PDK: **0 code changes** for the type itself. Only adjustments needed if the new type carries special semantics (e.g., a new mailbox-like rule).

**Touch points: 0 in `DeliveryOptions.php` for the standard case.**

---

## Adding a new DeliveryType

### Current

1. **SDK** — `RefTypesDeliveryTypeV2` enum entry.
2. **PDK** — `DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP`, `DELIVERY_TYPES_V2_MAP`, `DELIVERY_TYPES_NAMES` array, `DELIVERY_TYPES_IDS` array. (~5 touch points in `DeliveryOptions.php`.)
3. **CarrierSettings view** (`Frontend\View\CarrierSettingsItemView`) — currently iterates `CarrierSettings::ALLOW_*` constants. If the delivery type needs a toggle, add `CarrierSettings::ALLOW_<NEW_TYPE>`. (XCut B-4 will eliminate this once the loop becomes capability-driven.)
4. **Capabilities API** — emits the new type per-carrier where supported.
5. **CapabilitiesDeliveryTypeCalculator** — picks up the new type automatically through the capabilities flow.
6. **Calculators that have type-specific exceptions** (e.g. `DeliveryDateExceptionCalculator`): only touched if the new type interacts with the exception. Usually no change.

**Touch points: ~5 in `DeliveryOptions.php` + 1 in `CarrierSettings` if togglable + SDK + API.**

### Post-cleanup

After XCut B-4 (dynamic delivery-type loop) + INT-1441 (SDK mapping):

1. SDK adds it.
2. API emits it.
3. PDK: **0 code changes** for the type itself.

**Touch points: 0 in `DeliveryOptions.php`** for the standard case.

---

## Adding a new ShipmentOption

This is already the **most extensible** dimension and gets noticeably cleaner after the cleanup.

### Current

1. **SDK** — `RefShipmentShipmentOptions` and/or `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2`.
2. **PDK** — Create a `NewOptionDefinition` extending `AbstractOrderOptionDefinition`:
   - Implement `getShipmentOptionsKey()` (or return null if product-only).
   - Implement `getCapabilitiesOptionsKey()` (or return null if locally derived).
   - Override `getCarrierSettingsKey()`/`getProductSettingsKey()`/`getAllowSettingsKey()`/`getPriceSettingsKey()` only if defaults don't apply.
3. **Config** — register in `config/pdk-business-logic.php` `orderOptionDefinitions` array. **Skipping this step is what produced the 6 dead Definitions** (Schema A-1..A-6).
4. **Optionally** — add a dedicated calculator under `src/App/Order/Calculator/General/` if the option needs context-aware logic that `CapabilitiesOptionCalculator` can't express (rare; most options fit the generic pattern via reflection on `get<CapabilitiesKey>`).
5. **Capabilities API** — emits the new option in carrier responses (if it's a real exported option).

**Touch points: 1 new file + 1 config entry + SDK + API.**

### Post-cleanup

After Schema B-2 + Shipment B-3 (migrate deprecated consts to Definition keys) + Calculator B-1 (defensive `getCapabilityOption`):

1. SDK adds it.
2. API emits it.
3. **PDK: 1 new Definition file + 1 config entry.** Models auto-register the property; `ResolvesOptionAttributes` trait wires everything up. The defensive dispatch warns at boot/runtime if the `getCapabilitiesKey()` doesn't match an SDK method, catching typos early.

**Touch points: 2 PDK files** — that's the floor.

---

## Special-rule carriers (the awaiting-API cases)

If a new carrier needs a special rule that the capabilities API does not yet expose (e.g., "doesn't support delivery date", "requires customer info"), the **established pattern** is to isolate the rule in a scoped calculator file:

- `DeliveryDateExceptionCalculator` — hardcoded `CARRIERS_WITHOUT_DELIVERY_DATE`.
- `CustomerInformationCalculator` — hardcoded DPD branch.

**Cost for a new special-rule carrier:**

- If it joins an existing exception list: add 1 entry to a constant.
- If it introduces a new rule type: create a new scoped calculator file (5-10 lines) and register it in `config/pdk-business-logic.php` `orderCalculators`.

This is intentional design (not technical debt) until the capabilities API surfaces the missing field. Tracked in [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568).

---

## Adding plugin-side support

For any addition above that ships to merchants (carrier, type, option), the plugins typically need:

1. **Translation keys** — see `check-pdk-translations` skill for the automated path.
2. **Template adjustments** if the new dimension surfaces in UI (delivery-options widget, settings page).
3. **For new carriers:** plugin settings storage may need updating if the carrier has its own credentials/sub-account fields.

The PDK pieces above carry the data shape; the plugins carry the UX. They ship in coordinated releases.

---

## Friction summary

| Adding…              | Current touch points    | Post-cleanup touch points    |
| -------------------- | ----------------------- | ---------------------------- |
| Carrier              | 1 PDK file + SDK + API  | 0 PDK files (after INT-1441) |
| PackageType          | ~5 in `DeliveryOptions` | 0 PDK files                  |
| DeliveryType         | ~5 in `DeliveryOptions` | 0 PDK files                  |
| ShipmentOption       | 1 Definition + 1 config | Same — already at floor      |
| Special-rule carrier | 1 const entry / +1 file | Unchanged (intentional)      |

The cleanup eliminates "fan-out" friction (multiple constant maps to keep in sync) for carriers/types and keeps the already-clean ShipmentOption pattern at its current floor.
