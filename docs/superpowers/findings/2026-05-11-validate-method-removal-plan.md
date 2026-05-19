# `OrderOptionDefinitionInterface::validate()` — removal plan

Companion to `2026-05-11-v4-capabilities-cleanup-findings.md` §Schema architecture observations.

## Summary

`OrderOptionDefinitionInterface::validate(CarrierSchema $carrierSchema): bool` is declared on every `OrderOptionDefinition`. After the Definition cleanup (Schema A-1..A-6) the only production caller is `ExcludeParcelLockersCalculator::calculate()`. All other Definitions inherit `AbstractOrderOptionDefinition::validate()` which delegates to `CarrierSchema::canHaveShipmentOption($this)`.

Recommendation: **remove `validate()` from the contract and from `AbstractOrderOptionDefinition`; replace the one production call site with a direct `CarrierSchema::canHaveShipmentOption()` invocation.** The method is a redundant indirection.

## Current state

```
OrderOptionDefinitionInterface
└── validate(CarrierSchema $carrierSchema): bool      ← interface declaration
        ↑
        implemented by AbstractOrderOptionDefinition
        └── validate(CarrierSchema $cs): bool {
                return $cs->canHaveShipmentOption($this);   ← thin delegation
            }
                ↑
                overridden by 8 concrete Definitions that always return `true`
                (these overrides exist because the option is product-only or unconditional)
                ↑
                called by:
                    ExcludeParcelLockersCalculator::calculate()  ← ONLY production caller
```

After Schema A-1..A-6 cleanup: 6 of the 8 `return true` overrides disappear with the dead Definitions. The remaining 2 (`AgeCheckDefinition`? `PackageTypeDefinition`? — TBC on inspection) still override.

## Why remove it

1. **No abstraction earnings.** The interface method is a thin wrapper around `CarrierSchema::canHaveShipmentOption()`. The default implementation forwards directly; the few overrides return constant `true`. Neither adds logic that justifies a contract method.
2. **Single caller.** `ExcludeParcelLockersCalculator` is the sole consumer. Moving the check there inverts the dependency in a productive way — the calculator already has a `CarrierSchema`, just call `canHaveShipmentOption($definition)` directly.
3. **Hidden coupling.** `validate()` couples every Definition to `CarrierSchema`. Removing the method frees Definitions from the schema layer — important if Schema dissolves (see [`2026-05-11-carrierschema-architecture-decision.md`](2026-05-11-carrierschema-architecture-decision.md)).
4. **Strengthens the simpler shape.** Definitions become pure data declarations (keys + metadata). Behavior lives in the calculators that consume them.

## Removal sequence

1. **Prereq:** Schema A-1..A-6 deletions are merged (eliminates 6 of the override sites).
2. In `ExcludeParcelLockersCalculator`, replace `$definition->validate($carrierSchema)` with `$carrierSchema->canHaveShipmentOption($definition)`. Update the calculator's test accordingly.
3. Remove the `validate()` override from each remaining Definition class (`AgeCheckDefinition`, etc. — TBC after Schema A-cleanup).
4. Remove `validate()` from `AbstractOrderOptionDefinition`.
5. Remove `validate()` from `OrderOptionDefinitionInterface`.
6. Run `composer analyse` — confirm no stragglers.
7. Run `yarn run test` — full suite passes.
8. Plugin check: `rg '->validate\(' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/'` — confirm plugins don't call the method on a Definition.

## Impact

**PDK src/**

- 1 file modified (`ExcludeParcelLockersCalculator.php`): 1 line changed.
- 1 file modified (`AbstractOrderOptionDefinition.php`): `validate()` method removed (~3 lines).
- 1 file modified (`OrderOptionDefinitionInterface.php`): method declaration removed.
- ~2-8 Definition classes modified: override deleted (depends on Schema A cleanup).

**PDK tests/**

- ~3-10 tests reference `validate()` directly (factory tests, definition tests). Each test for `validate()` either:
  - Becomes a `CarrierSchema::canHaveShipmentOption()` test (one canonical place to test the schema delegation), OR
  - Is removed if it was only testing the override returning `true` (no longer meaningful).

**Plugins**

- Assumed zero impact (verify via the plugin grep in step 8). If plugins implement custom Definitions and override `validate()`, they need updating too — but our scan shows plugins consume Definitions, they don't implement them.

## Pros

- **Removes 1 contract method, ~5-10 implementations, ~3-10 tests.** Net symbol delta: significantly negative.
- **Simplifies Definition concept** to "metadata declaration", easier for newcomers to grasp.
- **Frees Definitions from `CarrierSchema`** — necessary precondition for the CarrierSchema dissolution path.
- **Eliminates redundant abstraction** — calculator-level direct calls are clearer than per-Definition method dispatch.

## Cons

- **One-time migration cost** in `ExcludeParcelLockersCalculator` and tests.
- **Slight API churn** for any external Definition implementation (none known; flagged as open question).
- **Less symmetry** between Definitions if some carried option-specific validation logic in the future. Mitigation: when that need arises, add a dedicated validator class rather than a contract method on every Definition.

## Open questions

- Are there Definitions registered by plugins (custom `OrderOptionDefinition` implementations)? PrestaShop/WooCommerce scan shows none, but a plugin developer could be writing one privately.
- Do we leave the option open to bring back `validate()` later if more complex validation rules emerge per-option? Recommendation: **no** — when that need arises, the right shape is a validator class, not an interface method on every Definition.

## Net simplicity delta

- Removes: 1 interface method, ~3 abstract-class lines, ~5-10 Definition overrides, ~3-10 tests.
- Adds: 0 (the calculator gains 1 direct method call replacing 1 indirect one — net zero at that site).
- **Net: -10 to -20 symbols** depending on Definition-override count after Schema A cleanup.
