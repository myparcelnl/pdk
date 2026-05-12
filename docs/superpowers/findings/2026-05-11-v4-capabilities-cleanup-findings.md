# v4-capabilities cleanup audit — findings

**Branch:** `chore/v4-capabilities-cleanup-audit` (HEAD `8752adb5`, baseline `main`)
**Audit date:** 2026-05-11
**Plugin cross-check refs:** `docker-prestashop@17e6e2c3`, `docker-wordpress@c75f5d9f`
**Re-baseline note:** 2 commits rebased into the branch after the initial agent run. Verifications below ran against current HEAD. The new commit `933c582d` ("feat(capabilities): hide capabilities not mapped to v1 CoreApi or delivery options") refactored `Carrier::filterRegisteredOptions` → `CapabilitiesService::filterSupportedCapabilities`; affected findings updated.

## Summary

| Pattern         | Dead (A) |               Optimize (B) | Architecture (C) |
| --------------- | -------: | -------------------------: | ---------------: |
| Platform        |        0 |                          1 |                4 |
| Carrier         |        0 | 0 (all deferred / dropped) |                4 |
| Schema          |       14 |                          2 |                4 |
| Validation      |        2 |                          2 |                4 |
| Calculator      |        0 |                          1 |                4 |
| Shipment Models |        5 |                          4 |                4 |
| Cross-cutting   |        0 |                          3 |                3 |

## Caveats

- Symbols referenced only via DI/config strings flagged with ⚠.
- Dynamic method calls (`__call`, variable method names) not detected by `rg` — flagged in evidence where relevant.
- Test-only references do not keep a symbol alive.
- PHPStan output was empty (composer analyse passed 0 errors); cross-check rests on agent rg evidence.
- All items below passed Gate A (per-finding human curation).

## Deliverables produced alongside this findings doc

- [`2026-05-11-six-unused-definitions-overview.md`](2026-05-11-six-unused-definitions-overview.md) — Schema A-1..A-6 per-item analysis
- [`2026-05-11-validate-method-removal-plan.md`](2026-05-11-validate-method-removal-plan.md) — plan + impact + pros/cons
- [`2026-05-11-carrierschema-architecture-decision.md`](2026-05-11-carrierschema-architecture-decision.md) — A/B writeup with recommendation
- [`2026-05-11-extension-cost-overview.md`](2026-05-11-extension-cost-overview.md) — adding Carrier / PackageType / DeliveryType / ShipmentOption
- [`2026-05-11-api-gaps-research-ticket-draft.md`](2026-05-11-api-gaps-research-ticket-draft.md) — Jira research ticket (Dutch). **Posted as [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568)** (status: Refinement).

---

## Platform

### A. Dead code

(no findings)

### B. Optimization candidates

**Platform B-1 — drop 3 empty PropositionConfig model classes**

- **Symbol(s):** `Pdk\Proposition\Model\PropositionI18nConfig`, `Pdk\Proposition\Model\PropositionRulesConfig`, `Pdk\Proposition\Collection\PropositionWeightCategoriesCollection`
- **Observation:** Empty classes only registered as typed casts in `PropositionConfig`. Plugin scan confirms PS and WC only access `propositionConfig->proposition->key`; `rules`, `weightCategories`, `internationalization` JSON keys are never read.
- **Proposed change:** Declare the corresponding `PropositionConfig` attributes as `array` (or `?array`); delete the 3 classes.
- **Simplicity delta:** Removes 3 files + 3 casts, Adds 0. Net: -3.
- **Plugin impact:** none verified (rg in PS + WC: 0 hits for `.rules`/`.weightCategories`/PropositionConfig access beyond `.proposition->key`).

### C. Architecture observations

1. **Single source of truth.** Proposition config loads from `config/proposition/proposition-{id}.json`, keyed by `platformId` from the Account API. No duplication. Constants in `Proposition` class map proposition keys to SDK platform names — single lookup point.
2. **Flow length.** Longest chain: `UpdateAccountAction` → `setActivePropositionId(account.platformId)` → `PropositionService` static cache → `getPropositionConfig()` → file load on first call (3-4 hops). Static-cached after first call.
3. **Misfits.** None in surviving code. `FALLBACK_PROPOSITION_ID` constant + TODO signals PDK can still run without a platform — pre-capabilities holdover but tracked under [INT-1479](<javascript:void(0)>). Don't touch in this audit.
4. **Extension cost.** Adding a new proposition: drop a JSON file in `config/proposition/`, add 2 entries to `Proposition` map constants. See extension-cost overview doc.

### Cross-pattern references

- (none)

---

## Carrier

### A. Dead code

(no findings)

### B. Optimization candidates

(no findings) — all proposed B items deferred to existing tickets or dropped per Gate A:

- Legacy name/ID maps (was B-1) → deferred to INT-1441 (SDK mapping centralization). See "Noted concerns".
- `CarrierRepository` private helpers (was B-2) → dropped. Single-call-site private helpers earn their place via intent clarity and testability.
- `CapabilitiesValidationService::getHighestMaxWeight()` (was B-3) → dropped (same rationale).

### C. Architecture observations

1. **Single source of truth.** Carriers flow from Account (Shop.carriers → `CarrierCollection`) through `AccountSettingsService::getCarriers()` (filtered via `Carrier::isSupported()`), then cached by `CarrierRepository`. Single entry point. No duplication.
2. **Flow length.** Read path: `model getter → trait → repository → cache hit` (3 hops). Write path: `UpdateAccountAction → CarrierCapabilitiesRepository::getContractDefinitions() → API → SDK deserialization → CarrierCollection → Account storage` (5 hops). Acceptable for a hydrate-once pattern.
3. **Misfits.** With the new `933c582d` commit, output-side filtering has moved from `Carrier::filterRegisteredOptions()` to `CapabilitiesService::filterSupportedCapabilities()` (private), made opt-in via `$filterSupported` flag. The user's concern about double/implicit output filtering is largely addressed — verify the remaining usage in `Carrier::attributesToArray()` is not still applying filtering implicitly on every serialization (cross-pattern with Schema/Validation: see Validation C).
4. **Extension cost.** Adding a new carrier: SDK adds to `RefCapabilitiesSharedCarrierV2`, server returns it in contract definitions, PDK adds `CARRIER_NAME_ID_MAP` entry (if V1 export-eligible), plugins redeployed. See extension-cost overview doc.

### Cross-pattern references

- `CapabilitiesValidationService` lives in `src/Carrier/Service/` but conceptually belongs to Validation. Tracked in Validation §C.
- `Carrier` legacy name/ID maps → INT-1441 will centralize in SDK.

### Open questions

- **Q (Calculator-related):** Verified `CarrierCapabilitiesRepository::getCapabilities()` cache key is `'capabilities.' . md5(json_encode($args))` — includes all dimensions (carrier, cc, packageType, deliveryType). Repository already correctly dimensions cache. No Carrier action needed.

---

## Schema

### A. Dead code

| ID          | Symbol                                      | Kind  | File                                                              | Evidence                                                                                                                                                                                       |
| ----------- | ------------------------------------------- | ----- | ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Schema A-1  | `CountryOfOriginDefinition`                 | class | `src/App/Options/Definition/CountryOfOriginDefinition.php`        | 0 src/ refs outside its own dir; not registered in `orderOptionDefinitions`; test-only usage                                                                                                   |
| Schema A-2  | `CustomsCodeDefinition`                     | class | `src/App/Options/Definition/CustomsCodeDefinition.php`            | same evidence as A-1                                                                                                                                                                           |
| Schema A-3  | `DisableDeliveryOptionsDefinition`          | class | `src/App/Options/Definition/DisableDeliveryOptionsDefinition.php` | same evidence; property `disableDeliveryOptions` is hand-declared in `ProductSettings`, so deleting the class does not break business logic                                                    |
| Schema A-4  | `FitInDigitalStampDefinition`               | class | `src/App/Options/Definition/FitInDigitalStampDefinition.php`      | same evidence                                                                                                                                                                                  |
| Schema A-5  | `FitInMailboxDefinition`                    | class | `src/App/Options/Definition/FitInMailboxDefinition.php`           | same evidence; the `fitInMailbox` _concept_ is alive via hand-declared `ProductSettings::FIT_IN_MAILBOX` consumed in `CartCalculationService`, but the Definition class itself is unreferenced |
| Schema A-6  | `PackageTypeDefinition`                     | class | `src/App/Options/Definition/PackageTypeDefinition.php`            | same evidence; `packageType` is hand-declared on `ProductSettings`                                                                                                                             |
| Schema A-7  | `CarrierSettings::EXPORT_HIDE_SENDER`       | const | `src/Settings/Model/CarrierSettings.php:148`                      | `@deprecated`. 0 refs in PDK src/tests/plugins (incl. self/static)                                                                                                                             |
| Schema A-8  | `CarrierSettings::EXPORT_RECEIPT_CODE`      | const | `src/Settings/Model/CarrierSettings.php:174`                      | same                                                                                                                                                                                           |
| Schema A-9  | `CarrierSettings::EXPORT_COLLECT`           | const | `src/Settings/Model/CarrierSettings.php:196`                      | same                                                                                                                                                                                           |
| Schema A-10 | `CarrierSettings::EXPORT_FRESH_FOOD`        | const | `src/Settings/Model/CarrierSettings.php:201`                      | same                                                                                                                                                                                           |
| Schema A-11 | `CarrierSettings::EXPORT_FROZEN`            | const | `src/Settings/Model/CarrierSettings.php:206`                      | same                                                                                                                                                                                           |
| Schema A-12 | `CarrierSettings::EXPORT_PRIORITY_DELIVERY` | const | `src/Settings/Model/CarrierSettings.php:211`                      | same                                                                                                                                                                                           |
| Schema A-13 | `CarrierSettings::PRICE_PRIORITY_DELIVERY`  | const | `src/Settings/Model/CarrierSettings.php:236`                      | same                                                                                                                                                                                           |
| Schema A-14 | `CarrierSettings::PRICE_COLLECT`            | const | `src/Settings/Model/CarrierSettings.php:243`                      | same                                                                                                                                                                                           |

See per-item recommendations in [`2026-05-11-six-unused-definitions-overview.md`](2026-05-11-six-unused-definitions-overview.md).

### B. Optimization candidates

**Schema B-1 — Remove the 6 dead Definition classes (A-1..A-6)**

- **Observation:** Six Definition classes are in `src/` but unregistered and unreferenced outside their own directory.
- **Proposed change:** Delete the 6 class files. Replace test references with the production equivalent (`ProductSettings::FIT_IN_MAILBOX`, etc.) used by `CartCalculationService` and similar.
- **Simplicity delta:** Removes 6 classes + their test factories where present. Net: ≤ -6.
- **Plugin impact:** None (verified — 0 refs in PS + WC).

**Schema B-2 — Migrate remaining `@deprecated` CarrierSettings consts to Definition keys**

- **Symbol(s):** `CarrierSettings::ALLOW_SIGNATURE`, `EXPORT_AGE_CHECK`, `EXPORT_INSURANCE` (17 refs), `EXPORT_LARGE_FORMAT`, `EXPORT_ONLY_RECIPIENT`, `EXPORT_RETURN`, `EXPORT_SIGNATURE`, `EXPORT_TRACKED`, `PRICE_ONLY_RECIPIENT`, `PRICE_SIGNATURE` — 10 alive-but-deprecated constants (~35 total call sites).
- **Observation:** Each carries `@deprecated now dynamically derived from XxxDefinition::getCarrierSettingsKey() / ::getPriceSettingsKey() / ::getAllowSettingsKey()`. Callers still reach for the const; the capabilities migration intended the Definition to be the single source.
- **Proposed change:** Migrate the ~35 call sites (including plugin code) to `OptionDefinition::getCarrierSettingsKey()` or appropriate variant, then delete the 10 consts. Plugin refactor in scope.
- **Simplicity delta:** Removes 10 consts + collapses 2 sources-of-truth into 1. Adds 0. Net: -10 (plus clarity win).
- **Plugin impact:** Both plugins reference `EXPORT_INSURANCE` (17 refs concentrated heavily there); other consts have 1-5 refs each. Plugin code-mod required.

### C. Architecture observations

1. **Single source of truth.** Option definitions are the authoritative declaration of option keys; `ResolvesOptionAttributes` trait auto-populates `$attributes`/`$casts` on `CarrierSettings`, `ProductSettings`, `ShipmentOptions`. The 6 dead definitions (A-1..A-6) reveal partial governance: some options were created as Definitions for orthogonality but the class never carried weight because the property was hand-declared elsewhere. The `validate()` method on `OrderOptionDefinitionInterface` is similarly underutilized — see plan in [validate() removal plan doc](2026-05-11-validate-method-removal-plan.md).
2. **Flow length.** `PdkOrderOptionsService::calculateShipmentOptions()` chains 4 helpers (ShipmentOptions → ProductSettings → CarrierSettings → CapabilitiesDefault). Each iterates `orderOptionDefinitions` config. Linear; clear.
3. **Misfits.** Deprecated constants in `CarrierSettings` and `ShipmentOptions` (~28 total — 8 dead consts here in Schema, 1 in Shipment Models, plus 10+17 migratable) are leftovers from the Definition-driven migration. Once Schema B-2 + Shipment B-3 land together, the "two-source-of-truth" smell resolves.
4. **Extension cost.** Adding a new shipment option: see extension-cost overview doc.

### Cross-pattern references

- **Validation** owns `CarrierSchema`, which `OrderOptionDefinitionInterface::validate()` calls into. See [CarrierSchema architecture decision doc](2026-05-11-carrierschema-architecture-decision.md).
- **Shipment Models §B-3** is the sibling workstream — migrate `ShipmentOptions::*` deprecated consts to Definition keys. Schema B-2 + Shipment B-3 should ship together.

---

## Validation

### A. Dead code

| ID             | Symbol               | Kind      | File                                             | Evidence                                                                                                              |
| -------------- | -------------------- | --------- | ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------- |
| Validation A-1 | `ValidatorInterface` | interface | `src/Validation/Contract/ValidatorInterface.php` | Extends dead `SchemaInterface`; never implemented or used; 0 call sites                                               |
| Validation A-2 | `SchemaInterface`    | interface | `src/Validation/Contract/SchemaInterface.php`    | Extended only by dead `ValidatorInterface`; `getSchema()` inherited by `CarrierSchema` but never called via interface |

### B. Optimization candidates

**Validation B-1 — Migrate to pure capabilities (deprecate `CarrierSchema`)**

- **Symbol(s):** `CarrierSchema` (class), `DeliveryOptionsValidatorInterface`, `CarrierSchema::canBeLetter()`, `::canBePackage()`, `::canBePackageSmall()`, `::canHaveExpressDelivery()` (4 untested wrappers), `::hasReturnCapabilities()` (PostNL-irrelevant stub), `::canHaveMondayDelivery()` (PostNL hardcode → see special note)
- **Observation:** `CarrierSchema` is `@deprecated` for capabilities migration. Per-pattern decision: migration to pure capabilities is in scope for cleanup; breaking changes acceptable.
- **Proposed change:** Detailed in the [CarrierSchema architecture decision doc](2026-05-11-carrierschema-architecture-decision.md). High-level: drop 4 untested wrappers; **fix `hasReturnCapabilities()` by using the directionality params that are already available in `/capabilities` — this is a PDK implementation gap, not an API gap**; **`canHaveMondayDelivery()` is NOT capabilities-bound** — Monday availability is a DeliveryOptions widget-only concern, doesn't power an API field, so it should either remain a hardcoded local fact about PostNL or be moved to the widget-feeding view code (not capabilities migration).
- **Simplicity delta:** Removes 1 interface, 1 deprecated class wrapper, 4 untested methods, ~20 callers updated. Adds 0 (callers go direct to `Carrier`/`CapabilitiesValidationService`). Net: significantly negative when complete.
- **Plugin impact:** PrestaShop has a `MockCarrierSchema` reference in test bootstrapper. Plugin code-mod required.

**Validation B-2 — Drop dead interfaces**

- **Symbol(s):** Validation A-1 (`ValidatorInterface`) and A-2 (`SchemaInterface`).
- **Proposed change:** Delete both. `getSchema()` was never called via the interface anyway.
- **Simplicity delta:** Removes 2 files + ~3 unused type hints. Net: -2.
- **Plugin impact:** none.

### C. Architecture observations

1. **Single source of truth.** Capabilities come from API (`RefCapabilitiesResponseCapabilityV2`) → hydrated into `Carrier`. `CarrierSchema` is a query API over `Carrier`; `CapabilitiesValidationService` provides weight/tier math. Two parallel data-access patterns over the same underlying data — the architectural decision doc proposes consolidating.
2. **Flow length.** Longest: `Frontend\View\CarrierSettingsItemView` → many `CarrierSchema` methods → `Carrier` (3 hops); each layer adds meaningful validation. After migration, expected: `view → Carrier` (2 hops) or `view → CapabilitiesValidationService → Carrier` (3 hops, only for non-trivial cases).
3. **Misfits.** `canHaveMondayDelivery()` PostNL hardcode is **deliberately not capabilities-shaped** — see B-1 explanation. `hasReturnCapabilities()` always-true stub is a **PDK implementation gap** — directionality is already in `/capabilities`; just consume it.
4. **Extension cost.** After CarrierSchema dissolution: new validation rule = method on `CapabilitiesValidationService` + API metadata, no schema layer to update. See extension-cost overview doc.

### Cross-pattern references

- See [CarrierSchema architecture decision doc](2026-05-11-carrierschema-architecture-decision.md) for the structured A/B decision.
- See [validate() removal plan doc](2026-05-11-validate-method-removal-plan.md) — the contract method that `CarrierSchema` is the sole consumer of.

---

## Calculator

### A. Dead code

(no findings)

### B. Optimization candidates

**Calculator B-1 — Defensive `getCapabilityOption()`**

- **Symbol(s):** `CapabilitiesOptionCalculator::getCapabilityOption()`
- **Observation:** Uses reflection `get<UcfirstKey>` to dispatch on the capabilities options object. If a `Definition::getCapabilitiesKey()` returns a key that doesn't resolve to a method, the dispatch fails silently — no warning, just no value. Misconfiguration is invisible.
- **Proposed change:** Verify the method exists on the options object before dispatch; if not, log a warning naming the `OptionDefinition` class and its `getCapabilitiesKey()` value.
- **Simplicity delta:** Removes 0, Adds 1 defensive check + 1 warning path. Net: +1. **User-justified override** of the simplicity guardrail — catches a real misconfiguration class that the reflection currently swallows.
- **Plugin impact:** none.

(All previously proposed Calculator B items dropped: shared cache helper duplicates `CapabilitiesRepository`'s own caching; the `DeliveryDateExceptionCalculator` and `CustomerInformationCalculator` hardcodes are deliberate scoped scaffolding; `TriStateOptionCalculator` stays separate for separation of concerns.)

### C. Architecture observations

1. **Single source of truth.** `CapabilitiesRepository::getCapabilities()` caches by `md5(json_encode($args))` — all dimensions covered. Each calculator reads the same source; no duplication.
2. **Flow length.** `PdkOrderOptionsService::calculate()` → `PdkOrderCalculator::calculateAll()` → iterates config-registered calculators → each calls `calculate()`. Capabilities lookup: 4 hops via `CapabilitiesValidationService` → repository → API (cached). Acceptable.
3. **Misfits.** Hardcoded carrier checks survive only inside intentionally scoped files (`DeliveryDateExceptionCalculator`, `CustomerInformationCalculator`) — these are awaiting-API placeholders. Tracked in [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568), not removed in this audit.
4. **Extension cost.** Adding a new shipment option requires only: Definition class + config registration + capabilities API key. See extension-cost overview doc.

### Cross-pattern references

- Schema/Definition pattern feeds Calculator inputs (option keys, validate, etc.).
- See [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568) for awaiting-API hardcodes.

---

## Shipment Models

### A. Dead code

| ID           | Symbol                                  | Kind  | File                                                                    | Evidence                                                                                                                                                     |
| ------------ | --------------------------------------- | ----- | ----------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Shipment A-1 | `DeliveryType`                          | class | `src/Shipment/Model/DeliveryType.php`                                   | 0 src/ refs; PS + WC scan returns only 1 hit in `tests/Datasets/orders.php` (test fixture). Migration to capabilities-driven types complete; class orphaned. |
| Shipment A-2 | `DeliveryTypeCollection`                | class | `src/Shipment/Collection/DeliveryTypeCollection.php`                    | same evidence; no data path hydrates it                                                                                                                      |
| Shipment A-3 | `DeliveryTypeFactory`                   | class | `tests/factories/Shipment/Model/DeliveryTypeFactory.php`                | test-only; 0 callers in test suite                                                                                                                           |
| Shipment A-4 | `DeliveryTypeCollectionFactory`         | class | `tests/factories/Shipment/Collection/DeliveryTypeCollectionFactory.php` | same                                                                                                                                                         |
| Shipment A-5 | `ShipmentOptions::ALL_SHIPMENT_OPTIONS` | const | `src/Shipment/Model/ShipmentOptions.php:137`                            | `@deprecated Use option definitions to determine available shipment options dynamically instead`. 0 refs in PDK src/tests/plugins (incl. self/static).       |

### B. Optimization candidates

**Shipment B-1 — Drop `PackageType` model wrapper for `WeightService`**

- **Symbol(s):** `PackageType` (class), `PackageTypeCollection`, `PackageTypeFactory`
- **Observation:** `PackageType` is instantiated in 3 sites (`WeightCalculator`, `CapabilitiesPackageTypeCalculator`, `CartCalculationService`) solely to wrap a string name extracted via `$packageType->name` in `WeightService::getEmptyWeightForPackageType()`.
- **Proposed change:** Pass `string $packageTypeName` directly to `WeightService`; delete the wrapper class and collection.
- **Simplicity delta:** Removes 3 instantiations + 1 class + 1 collection + 1 factory. Net: -6 symbols.
- **Plugin impact:** None (verified — 0 refs in PS + WC).

**Shipment B-2 — Inline `RetailLocationType`**

- **Symbol(s):** `RetailLocationType`
- **Observation:** Never instantiated; only referenced as a class-string cast on `RetailLocation::casts['type']`. No live data path populates `type` as anything other than a string from SDK.
- **Proposed change:** Inline the validation into `RetailLocation`; make `type` a plain string with no cast. **Do NOT convert to PHP enum** (PHP 7.4 remains the lowest supported version).
- **Simplicity delta:** Removes 1 file, Adds 0. Net: -1.
- **Plugin impact:** none.

**Shipment B-3 — Migrate `@deprecated` `ShipmentOptions::*` consts to Definition keys**

- **Symbol(s):** 17 `@deprecated` consts: `INSURANCE`, `AGE_CHECK`, `DIRECT_RETURN`, `HIDE_SENDER`, `LARGE_FORMAT`, `ONLY_RECIPIENT`, `PRIORITY_DELIVERY`, `RECEIPT_CODE`, `SAME_DAY_DELIVERY`, `SATURDAY_DELIVERY`, `MONDAY_DELIVERY`, `SIGNATURE`, `TRACKED`, `COLLECT`, `EXCLUDE_PARCEL_LOCKERS`, `FRESH_FOOD`, `FROZEN`, `COOLED_DELIVERY` (~37 callers total).
- **Observation:** Each `@deprecated Use definition's getShipmentOptionsKey() instead`. Definition is the intended single source of truth.
- **Proposed change:** Migrate ~37 call sites (PDK + plugins) to `OptionDefinition::getShipmentOptionsKey()`; delete the 17 consts. Ship together with Schema B-2.
- **Simplicity delta:** Removes 17 consts + collapses 2 sources-of-truth into 1. Net: -17.
- **Plugin impact:** Both plugins (especially `INSURANCE`, `SIGNATURE`). Plugin code-mod required.

**Shipment B-4 — `DropOffDay`/`DropOffDayCollection`**

- **Symbol(s):** `DropOffDay`, `DropOffDayCollection`
- **Observation:** Alive but specialized — used only for pickup delivery type via `DropOffService::getPossibleDropOffDays()`.
- **Proposed change:** None; leave as-is. Net: 0. Listed for visibility.
- **Plugin impact:** none.

### C. Architecture observations

1. **Single source of truth.** Post-v4: delivery and package types come from the capabilities endpoint (CarrierCapability definitions). `DeliveryOptions` consumes type _names_ (strings) and maps via SDK V2 enum constants. `DeliveryType` and `DeliveryTypeCollection` are orphans of the legacy approach.
2. **Flow length.** `DeliveryOptions` hydrated via `fromCapabilitiesDefinitions()` — no intermediate `DeliveryType` model. `ShipmentOptions` similarly hydrated from capabilities. `RetailLocation` and `PhysicalProperties` hydrate via Shipment property casts.
3. **Misfits.** `DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP` and `PACKAGE_TYPES_NAMES_IDS_MAP` are hardcoded and not auto-synced with capabilities. Dual V1/V2 SDK enum versions are maintained in parallel for DeliveryType, PackageType, and ShipmentOptions (not just Carrier). **Out of scope here** — dynamic SDK mapping is planned under INT-1441 and should cover all four dimensions (Carrier + DeliveryType + PackageType + ShipmentOptions).
4. **Extension cost.** Adding a new delivery type or package type today: 5-6 touch points across `DeliveryOptions` constants and maps. After INT-1441: closer to 1 touch point in the SDK. See extension-cost overview doc.

### Cross-pattern references

- See `Calculator B-1` for `CapabilitiesOptionCalculator` defensive check related to dynamic dispatch.
- See `Schema B-2` for the sibling const-migration workstream.

---

## Cross-cutting capabilities-migration markers

### A. Dead code

(no findings)

### B. Optimization candidates

**XCut B-1 — Deprecate `MyParcelApiService` in place**

- **Symbol(s):** `Pdk\Api\Service\MyParcelApiService`
- **Observation:** Class-level `@deprecated` says to use `SdkApi/Service/CoreApi/`. Caller migration is INT-1266's scope.
- **Proposed change:** Keep the `@deprecated` annotation; do not migrate callers in this audit. INT-1266 owns the full SDK migration.
- **Simplicity delta:** 0 (status quo). Visibility win only.
- **Plugin impact:** none in this audit.

**XCut B-2 — Replace `Account::FEATURE_ORDER_NOTES`**

- **Symbol(s):** `Pdk\Account\Model\Account::FEATURE_ORDER_NOTES`
- **Observation:** `@deprecated Use \MyParcelNL\Sdk\Client\Generated\IamApi\Model\Feature::ORDER_NOTES instead`.
- **Proposed change:** Replace PDK const references with the SDK enum; delete the PDK const. Plugin code-mod required.
- **Simplicity delta:** Removes 1 const. Net: -1.
- **Plugin impact:** likely both plugins. In scope.

**XCut B-3 — Replace `WeightServiceInterface::DIGITAL_STAMP_RANGES`**

- **Symbol(s):** `Pdk\Base\Contract\WeightServiceInterface::DIGITAL_STAMP_RANGES`
- **Observation:** `@deprecated use Pdk::get('digitalStampRanges'). Will be removed in v3.0.0`.
- **Proposed change:** Replace direct const refs with `Pdk::get('digitalStampRanges')`; delete the const. Plugin code-mod required.
- **Simplicity delta:** Removes 1 const. Net: -1.
- **Plugin impact:** both plugins. In scope.

**XCut B-4 — Make `CarrierSettingsItemView` delivery-type loop dynamic**

- **Symbol(s):** `CarrierSettingsItemView` (line ~546-548) — iterates `CarrierSettings::ALLOW_*` constants with `@TODO: in the future, make this fully dynamic`.
- **Observation:** Capabilities-driven `Carrier.deliveryTypes` should drive the loop instead. May also apply to packageType.
- **Proposed change:** Iterate `Carrier.deliveryTypes` (and packageTypes) from capabilities; centralize the `'allow' + ucfirst($type)` key construction in PDK so no invalid key lookups can occur via drift. Inventory existing `allowX` constructions before generalizing.
- **Simplicity delta:** Removes per-type `ALLOW_*` constants from the iteration footprint; Adds 1 helper / iteration. Net: -N (N = number of ALLOW\_\* delivery-type constants).
- **Plugin impact:** none (PDK-internal view).

### C. Architecture observations

1. **Migration completeness signal.** ~40 capability-migration `@deprecated`/`@TODO` markers remain across `ShipmentOptions`, `CarrierSettings`, and `CarrierSchema`. The cleanup plans should land in sequence so that, post-execution, the remaining markers can be deleted — signaling true migration completion.
2. **Hardcode markers — three categories:**
   - **PDK implementation gap (fix in this audit's plans):** `hasReturnCapabilities()` — directionality is already exposed by `/capabilities`; the always-true stub just needs to consume it. Goes into the Validation/CarrierSchema dissolution plan, NOT the research ticket.
   - **Non-capabilities, awaiting API discussion:** `DeliveryDateExceptionCalculator` (BPost/DPD) and `CustomerInformationCalculator` (DPD) need data that is not capabilities-shaped — likely metadata/validation layer. Tracked in [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568).
   - **Local-only, NOT an API concern:** `canHaveMondayDelivery()` is a DeliveryOptions widget-only feit; will be relocated as part of the CarrierSchema dissolution, not driven from an API.
3. **In-flight ticket coordination.** INT-1266 (SDK migration), INT-1441 (mapping centralization), INT-1479 (default carrier refactor). Audit defers overlapping items to these tickets.

---

## Noted concerns (not promoted to plans)

- **Carrier legacy name/ID maps** (was Carrier B-1) — 14 `CARRIER_*_LEGACY_NAME` constants + 2 maps. Will be addressed by INT-1441 (SDK-side mapping centralization). Follow-up "dynamic mapping retrieved from the API itself" is part of [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568)'s legacy V1↔V2 mapping point.
- **Non-capabilities API hardcodes (need API-team discussion):** `DeliveryDateExceptionCalculator` (BPost, DPD), `CustomerInformationCalculator` (DPD). Not capabilities-shaped — likely metadata/validation. Tracked in [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568).
- **PDK implementation gap (fix in plans):** `CarrierSchema::hasReturnCapabilities()` always-true stub. Directionality is already available in `/capabilities`; consume it in the Validation/CarrierSchema dissolution plan.
- **`canHaveMondayDelivery()` PostNL hardcode** — explicitly NOT a capabilities migration target. DeliveryOptions widget-only concern; doesn't power any API field. Keep hardcoded or relocate to widget-feeding view code as part of the CarrierSchema architectural decision.
- **PHP 7.4 baseline.** Several enum-conversion candidates exist (`RetailLocationType`, hardcoded delivery/package type maps) but are blocked on the PHP 7.4 minimum. Re-evaluate when PHP 8.1+ becomes the floor.
- **Dynamic SDK V1↔V2 mapping** — covers Carrier, DeliveryType, PackageType, and ShipmentOptions. Out of scope here; INT-1441 covers all four.

---

## Recommended plan slicing

Each plan below should be a separate `superpowers:writing-plans` invocation, written on `chore/v4-capabilities-cleanup-audit` and gated on per-item Q&A before commit.

- **Plan: Platform cleanup** — Platform B-1.
- **Plan: Schema cleanup (part 1, dead Definitions)** — Schema A-1..A-6 + Schema B-1 + test migration to production-equivalent property access.
- **Plan: Schema + Shipment shared (const migration)** — Schema A-7..A-14 + Schema B-2 + Shipment A-5 + Shipment B-3. Ship together; plugin refactors included.
- **Plan: Validation + CarrierSchema dissolution** — Validation A-1, A-2, B-1, B-2 + drive the architectural decision from [the decision doc](2026-05-11-carrierschema-architecture-decision.md). Includes `hasReturnCapabilities` directionality migration and removal of 4 untested `canBe*()` wrappers. `canHaveMondayDelivery` decision included (likely relocated, not migrated).
- **Plan: validate() method removal** — per [the plan doc](2026-05-11-validate-method-removal-plan.md). Touches both Schema and Validation.
- **Plan: Shipment Models cleanup (DeliveryType + PackageType + RetailLocationType)** — Shipment A-1..A-4, B-1, B-2, B-4. Tests need updates (1 WC test dataset).
- **Plan: Calculator defensive dispatch** — Calculator B-1.
- **Plan: Cross-cutting const migrations** — XCut B-2 (`FEATURE_ORDER_NOTES`), XCut B-3 (`DIGITAL_STAMP_RANGES`), XCut B-4 (CarrierSettingsItemView dynamic loop). Plugin code-mods included.

(`MyParcelApiService` deprecation is XCut B-1 — already deprecated in place; no plan item beyond visibility.)

---

## Open questions for human review

- **Confirm INT-1266 / INT-1441 / INT-1479 ticket scope.** The audit defers a number of items to these tickets — verify each is in fact owned by the corresponding ticket before the plans run.
- **API gaps research ticket** (Jira, type: research). Posted as [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568); draft preserved in [the ticket draft doc](2026-05-11-api-gaps-research-ticket-draft.md) for traceability.
- **Plugin migration timing.** All plugin code-mods are bundled with their corresponding PDK plan rather than getting a deprecation grace period. Confirm this is what you want before any plan ships.
- **`canHaveMondayDelivery` relocation target.** Where should the PostNL hardcode live if not in `CarrierSchema` — the widget-feeding view, or somewhere closer to DeliveryOptions data shaping? Pinned in the CarrierSchema decision doc.
