# `CarrierSchema` — architectural decision

Companion to `2026-05-11-v4-capabilities-cleanup-findings.md` §Validation.

## Context

`Pdk\Validation\Validator\CarrierSchema` is class-level `@deprecated`:

> "This will be replaced with generic capabilities-focussed functionality in the future."

But it remains heavily used: ~20+ call sites across `AbstractOrderOptionDefinition::validate()`, `Frontend\View\CarrierSettingsItemView`, `ExcludeParcelLockersCalculator`, etc. It carries three categories of method:

1. **Capability proxies.** Methods like `hasDeliveryType(DeliveryTypeV2::EVENING)`, `canHaveShipmentOption(Definition)`, `canHavePackageType(...)` — these read directly from `Carrier` data with no extra logic.
2. **Composed query helpers.** Methods like `canBeDigitalStamp()`, `canBeMailbox()`, etc. — wrap one or two capability proxies with a domain-specific name.
3. **Local-knowledge stubs and PDK implementation gaps.** `canHaveMondayDelivery()` (PostNL hardcode — explicitly NOT capabilities-shaped, widget-only concern). `hasReturnCapabilities()` (always-`true` stub — **directionality IS already available in `/capabilities`; this is a PDK implementation gap, not an API gap**).

The user explicitly asked: _"either define a clean purpose for the schema, or remove it and ensure its functionality is refactored into other classes."_ This document drives that decision.

## Option A — Define a clean purpose

**Purpose:** `CarrierSchema` is the **read-side façade over `Carrier` for view code and calculators that don't need the model directly**.

Under this option:

- Category 1 (capability proxies) stays — that's the façade.
- Category 2 (composed helpers like `canBeMailbox`) stays only where a _real_ second caller benefits. Untested wrappers (`canBeLetter`, `canBePackage`, `canBePackageSmall`, `canHaveExpressDelivery`) are deleted.
- Category 3 leaves:
  - `canHaveMondayDelivery` → relocated to the DeliveryOptions widget-feeding code (likely `CarrierSettingsItemView` or a new small helper). Hardcoded PostNL knowledge doesn't belong in a "schema".
  - `hasReturnCapabilities` → reimplemented to consult the directionality params already exposed by `/capabilities`. Stays in `CarrierSchema` as a query. (PDK implementation gap fix; not blocked on API work.)
- `@deprecated` class annotation removed; replaced with a class-level doc stating the new purpose.

### Pros

- **Lowest churn.** Most callers stay; the schema retains its name and most of its surface.
- **Clear contract.** "Façade over Carrier" is an honest, single-purpose description.
- **Easy migration.** Each removed/relocated method is a focused change.

### Cons

- **`CarrierSchema` survives as a layer.** The audit's north star is "fewer layers" — keeping the schema requires it to earn its place. The façade earns it if at least one caller benefits from the named abstraction.
- **Risk of drift.** If new methods are added carelessly, the schema accumulates incidental helpers again. A class-level docblock + lint helps.
- **The `validate()` removal plan still applies** — `AbstractOrderOptionDefinition::validate()` still routes through `CarrierSchema::canHaveShipmentOption()`. Removing `validate()` means definitions stop using the schema, but `CarrierSettingsItemView` and `ExcludeParcelLockersCalculator` keep using it.

### Symbol delta (Option A)

- Removes: 4 untested `canBe*` wrappers + 1 method relocated out (`canHaveMondayDelivery`) + `DeliveryOptionsValidatorInterface` + 2 dead interfaces (`ValidatorInterface`, `SchemaInterface`).
- Adds: 1 implementation update to `hasReturnCapabilities` (no new symbol — same method, new body).
- Net: -8 symbols + 1 reshaped method. Clarity win from removing the `@deprecated`.

## Option B — Dissolve and reroute

**Purpose:** `CarrierSchema` becomes 0 classes; its remaining methods are absorbed into the most natural caller-side homes.

Under this option:

- Category 1 (capability proxies): caller-side direct access. `CarrierSettingsItemView`, `ExcludeParcelLockersCalculator`, etc. call methods on `Carrier` directly: `$carrier->hasDeliveryType(...)`, `$carrier->canHaveShipmentOption(...)`. These methods migrate from `CarrierSchema` to `Carrier`.
- Category 2 (composed helpers): inlined at call sites where they survive at all.
- Category 3:
  - `canHaveMondayDelivery` → relocated as in Option A.
  - `hasReturnCapabilities` → method on `CapabilitiesValidationService::hasReturnCapabilities(Carrier, DirectionalityContext)` (where heavier capability math already lives). Consumes the directionality params already in `/capabilities` — PDK implementation gap, not blocked on API.
- `CarrierSchema` class deleted. `DeliveryOptionsValidatorInterface` deleted. `ValidatorInterface` and `SchemaInterface` already dead (Validation A-1/A-2) — deleted.

### Pros

- **One fewer layer.** Matches the audit's north star directly.
- **Clearer responsibilities.** `Carrier` owns "what can I do?" queries (the carrier IS its capability snapshot now). `CapabilitiesValidationService` owns capability-math (weights, tiers, directionality). No middle layer.
- **Definitions decouple from the schema** by design — supports the `validate()` removal plan naturally.
- **Net simplicity gain is larger.**

### Cons

- **Higher churn.** ~20 callers need updating. Plugin code-mod required (PS has a `MockCarrierSchema` in test bootstrapper; WC may have references).
- **`Carrier` model grows.** Adding ~10 query methods to `Carrier` could push it past comfortable size. Counter-argument: the methods are tiny one-liners reading capability data the model already holds — they belong on the model.
- **Loss of a named query layer.** Some readers find "ask the schema" mentally cleaner than "ask the carrier itself". Trade-off accepted.

### Symbol delta (Option B)

- Removes: `CarrierSchema` class + ~10 methods (those that survive get rehomed onto `Carrier` or `CapabilitiesValidationService`, so the _concept_ moves but the _class_ goes), `DeliveryOptionsValidatorInterface`, 2 dead interfaces, ~4 untested wrappers.
- Adds: ~5-7 methods on `Carrier` (the surviving capability proxies). Some are pure relocations (no net new code).
- Net: -1 class layer, ~-10 net symbols, ~20 caller updates.

## Recommendation

**Option B.** It aligns with the audit's stated goal of net simplification and "fewer layers"; the surviving methods all have natural homes; the `@deprecated` marker the original team left tells us they intended this direction.

The churn is bounded — ~20 callers across PDK + plugins, all mechanical edits (replace `$schema->method()` with `$carrier->method()` or `$capabilitiesValidationService->method()`). The plan can include an automated `rg` + `sed` step for the bulk of edits, with manual review of the relocations for `canHaveMondayDelivery` and `hasReturnCapabilities`.

If the churn estimate proves too high during plan execution, **Option A is a clean fallback** — same individual deletions/relocations, just keep the schema class as the named façade and migrate fewer callers.

### Decision input the user should sign off on

1. Confirm Option B (recommended) vs Option A.
2. Pick relocation target for `canHaveMondayDelivery`:
   - `CarrierSettingsItemView` (closer to where it's actually consumed),
   - A new tiny helper class (e.g. `MondayDeliveryAvailability::for(Carrier)`),
   - Or inline it (probably the cleanest for one-line hardcoded fact).
3. Confirm the directionality implementation approach for `hasReturnCapabilities` — should it live on `CapabilitiesValidationService` or on `Carrier` directly?
