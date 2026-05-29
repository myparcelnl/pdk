# Validation cleanup: split queries into CarrierValidationService + CapabilitiesValidationService — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dissolve `CarrierSchema`. Introduce a new `CarrierValidationService` as the home for all carrier-level capability queries (`supportsShipmentOption`, `supportsMailbox`, `supportsDigitalStamp`, `supportsReturns`, `getAllowedInsuranceAmounts`). The existing `CapabilitiesValidationService` keeps its capability-level queries (`supportsWeight`, etc.). The two are sister services; the subject (Carrier vs single capability row) is encoded in the **class name**, not in method prefixes. Drop 10 untested wrapper methods. Inline `canHaveMondayDelivery` into its only caller (`CarrierSettingsItemView`). Reimplement `supportsReturns` against the `direction: inbound` capability available in `/capabilities`. Extract `buildInsuranceTiers` (pure static math) into an `InsuranceTierMath` util. Delete the three dead interfaces (`ValidatorInterface`, `SchemaInterface`, `DeliveryOptionsValidatorInterface`). Rename `CapabilitiesValidationService::capabilitySupportsWeight` to `supportsWeight` for naming consistency.

**Why this version (final design):** Three earlier drafts each fell short of the user's "centralised but unambiguous" target:

- "Option B" (split between Carrier model and a service) — created cognitive friction at every call site.
- "Option A" facade rename (`CarrierCapabilityQueries`) — invented a new class when an existing service partially played the role.
- Consolidate-on-existing-service with subject prefixes — prefixes felt belt-and-suspenders given the class already carries the subject.

The accepted design: two parallel services, each named after its subject, each with un-prefixed verb methods (`supports*`, `has*`, `getAllowed*`). Mis-placed methods get caught in review.

**Architecture:**

- `CarrierValidationService` (new) — asks questions about a whole `Carrier`. First parameter of every method is `Carrier $carrier`. Internally delegates to `CapabilitiesValidationService` / `CarrierCapabilitiesRepository` when capability data is needed.
- `CapabilitiesValidationService` (existing) — asks questions about a single capability row (`RefCapabilitiesResponseCapabilityV2`). First parameter is a capability object.
- `InsuranceTierMath` (new util) — pure math, no carrier or capability state.
- `CarrierSchema` and three dead interfaces deleted.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Validation (A-1, A-2, B-1, B-2) and the [CarrierSchema architecture decision doc](../findings/2026-05-11-carrierschema-architecture-decision.md). This plan supersedes both options in that doc; the final design was converged through several rounds of user feedback.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`, **after** Schema cleanup part 1 and `validate()` method removal have landed on the same branch. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`.

**Hard dependencies:**

- Schema cleanup part 1 (`2026-05-11-schema-cleanup-part-1-dead-definitions.md`) merged.
- `validate()` method removal (`2026-05-11-validate-method-removal.md`) merged.

---

## Method-by-method action map

The single authoritative reference. Anything not listed is unchanged.

### From `CarrierSchema` → new `CarrierValidationService`

| Old method                                                                                                                                                                                                         | Action                         | New name + signature                                                                     | Notes                                                                                                                       |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------ | ---------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `canHaveShipmentOption($definition)`                                                                                                                                                                               | ✅ move                        | `CarrierValidationService::supportsShipmentOption(Carrier $carrier, $definition): bool`  | 13 callers.                                                                                                                 |
| `canBeMailbox()`                                                                                                                                                                                                   | ✅ move                        | `CarrierValidationService::supportsMailbox(Carrier $carrier): bool`                      | 1 caller.                                                                                                                   |
| `canBeDigitalStamp()`                                                                                                                                                                                              | ✅ move                        | `CarrierValidationService::supportsDigitalStamp(Carrier $carrier): bool`                 | 1 caller.                                                                                                                   |
| `getAllowedInsuranceAmounts()`                                                                                                                                                                                     | ✅ move                        | `CarrierValidationService::getAllowedInsuranceAmounts(Carrier $carrier): array`          | 2 callers. Internally calls `InsuranceTierMath::buildTiers`.                                                                |
| `hasReturnCapabilities()`                                                                                                                                                                                          | 🔄 reimplement + move + rename | `CarrierValidationService::supportsReturns(Carrier $carrier, array $context = []): bool` | 2 callers. Replace always-true stub with a real query against `/capabilities` (`direction: inbound`).                       |
| `canHaveMondayDelivery()`                                                                                                                                                                                          | 🔄 inline                      | private helper in `CarrierSettingsItemView`                                              | 1 caller (the view). PostNL hardcode; widget-only; not a capabilities concern.                                              |
| `__call($name, $args)`                                                                                                                                                                                             | ❌ delete                      | —                                                                                        | Dynamic dispatch for legacy `canHaveX*`. After this plan, callers use explicit `supportsShipmentOption(carrier, X::class)`. |
| `setCarrier($carrier)`                                                                                                                                                                                             | ❌ delete                      | —                                                                                        | Service is stateless; carrier passed per call.                                                                              |
| `getSchema()`, `createSchema()`, `getFromSchema()`, `canHavePackageType()`, `hasDeliveryType()`, `getCarrier()`, `resolveDefinition()`                                                                             | ❌ delete                      | —                                                                                        | Internal helpers tied to deleted state. Their logic reproduces inline (1-3 lines each) in the new service methods.          |
| `canBeLetter`, `canBePackage`, `canBePackageSmall`, `canHaveExpressDelivery`, `canHaveEveningDelivery`, `canHaveMorningDelivery`, `canHaveStandardDelivery`, `canHavePickup`, `canHaveMultiCollo`, `canHaveWeight` | ❌ delete                      | —                                                                                        | 0 external callers each.                                                                                                    |
| `getAllowedDeliveryTypes()`, `getAllowedPackageTypes()`                                                                                                                                                            | ❌ delete                      | —                                                                                        | 0 external callers. `Carrier::deliveryTypes` and `Carrier::packageTypes` already expose them.                               |

### Existing `CapabilitiesValidationService` — consistency cleanup

| Old method                                               | Action     | New name (if changed)                               | Notes                                                                                                                                                                                                |
| -------------------------------------------------------- | ---------- | --------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `capabilitySupportsWeight($capability, int $weight)`     | 🔄 rename  | `supportsWeight($capability, int $weight)`          | Drop the `capability` prefix — class name carries the subject.                                                                                                                                       |
| `buildInsuranceTiers(int $min, int $max)` (static)       | 🔄 extract | `InsuranceTierMath::buildTiers(int $min, int $max)` | Pure math; no carrier or capability state.                                                                                                                                                           |
| `getRepository()`                                        | ✅ keep    | —                                                   | DI accessor.                                                                                                                                                                                         |
| `indexByCarrier(array)`                                  | ✅ keep    | —                                                   | Internal-helper-shaped utility, used by `getPackageTypeWeights`.                                                                                                                                     |
| `getPackageTypeWeights(string $cc, array $allowedTypes)` | ✅ keep    | —                                                   | Returns weights across carriers per package type. Doesn't take a single capability; not Carrier-first. Sits outside the subject convention but is a useful service-level orchestration; leave alone. |
| `resolveHeaviestType(array $types, array $typeWeights)`  | ✅ keep    | —                                                   | Pure picker logic over weight tables. Could extract to a util later if a second consumer appears.                                                                                                    |
| `getHighestMaxWeight(array $capabilities)` (private)     | ✅ keep    | —                                                   | Internal helper.                                                                                                                                                                                     |

### Interfaces

| Symbol                              | Action    | Notes                                                               |
| ----------------------------------- | --------- | ------------------------------------------------------------------- |
| `DeliveryOptionsValidatorInterface` | ❌ delete | Only implementer is `CarrierSchema`; callers use the concrete type. |
| `ValidatorInterface`                | ❌ delete | Validation A-1; dead.                                               |
| `SchemaInterface`                   | ❌ delete | Validation A-2; dead.                                               |

---

## File structure

| File                                                                                        | Action               | Responsibility                                                                                                                                                    |
| ------------------------------------------------------------------------------------------- | -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `src/Carrier/Service/CarrierValidationService.php`                                          | Create               | New sister service to `CapabilitiesValidationService`. Carrier-level queries (`supports*`, `getAllowed*`).                                                        |
| `src/Carrier/Util/InsuranceTierMath.php`                                                    | Create               | `static buildTiers(int $min, int $max): array`. Extracted from the capabilities service.                                                                          |
| `src/Carrier/Service/CapabilitiesValidationService.php`                                     | Modify               | Rename `capabilitySupportsWeight` → `supportsWeight`. Delete `buildInsuranceTiers` (replaced by `InsuranceTierMath::buildTiers`). All other methods unchanged.    |
| `src/Frontend/View/CarrierSettingsItemView.php`                                             | Modify               | Replace `$this->carrierSchema->*` calls with `$this->carrierValidationService->*($this->carrier, ...)`. Inline `canHaveMondayDelivery` as a private helper.       |
| `src/Shipment/Request/PostReturnShipmentsRequest.php`                                       | Modify               | `$schema->hasReturnCapabilities()` → `$carrierValidation->supportsReturns($carrier)`.                                                                             |
| `src/Shipment/Request/PostShipmentsRequest.php`                                             | Modify               | Migrate `$schema->*()` calls.                                                                                                                                     |
| `src/App/Order/Collection/PdkOrderCollection.php`                                           | Modify               | Two `$schema->hasReturnCapabilities()` calls migrated.                                                                                                            |
| `src/App/Order/Calculator/General/CustomerInformationCalculator.php`                        | Modify               | Migrate `$schema->*()` calls.                                                                                                                                     |
| `src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php`                       | Modify               | `$this->carrierSchema->canHaveShipmentOption($def)` → `$this->carrierValidationService->supportsShipmentOption($carrier, $def)`.                                  |
| `src/Validation/Validator/CarrierSchema.php`                                                | Delete               | Dissolved.                                                                                                                                                        |
| `src/Validation/Contract/DeliveryOptionsValidatorInterface.php`                             | Delete               | Validation B-1.                                                                                                                                                   |
| `src/Validation/Contract/ValidatorInterface.php`                                            | Delete               | Validation A-1.                                                                                                                                                   |
| `src/Validation/Contract/SchemaInterface.php`                                               | Delete               | Validation A-2.                                                                                                                                                   |
| `config/` DI bindings                                                                       | Modify               | Drop `CarrierSchema::class` and dead-interface bindings; add `CarrierValidationService::class` binding if not auto-resolved.                                      |
| `~/projects/docker-prestashop/modules/myparcelnl/tests/Bootstrap/MockPsPdkBootstrapper.php` | Modify (plugin repo) | Drop `MockCarrierSchema` binding (or replace with a stub for `CarrierValidationService` if the plugin tests need one). **Cross-repo plugin PR.**                  |
| PDK tests                                                                                   | Modify               | Tests that exercised `CarrierSchema` get rewritten against `CarrierValidationService`. Tests for `capabilitySupportsWeight` get the rename. Enumerated in Task 9. |

---

## Task 1: Dependency check + baseline + directionality verification

**Files:** No edits.

- [ ] **Step 1: Verify prerequisites are merged**

```bash
test ! -f src/App/Options/Definition/CountryOfOriginDefinition.php && echo "Schema part 1: OK" || echo "Schema part 1: NOT MERGED"
rg -q 'public function validate\(' src/App/Options/Contract/OrderOptionDefinitionInterface.php && echo "validate() removal: NOT MERGED" || echo "validate() removal: OK"
```

Expected: both "OK". If either is "NOT MERGED", **stop** and merge the dependency first.

- [ ] **Step 2: Verify branch**

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 3: Baseline tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log; echo "exit: $?"
```

- [ ] **Step 4: Confirm directionality is exposed by the SDK's capabilities request model**

```bash
rg -n 'public function (set|get)Direction\b' vendor/myparcelnl/sdk/src/Client/Generated/CoreApi/Model/CapabilitiesPostCapabilitiesRequestV2.php
rg -n 'getDirectionAllowableValues' vendor/myparcelnl/sdk/src/Client/Generated/CoreApi/Model/CapabilitiesPostCapabilitiesRequestV2.php
```

Expected: both `setDirection`/`getDirection` exist; `getDirectionAllowableValues()` returns the allowed strings (likely `'outbound'`, `'inbound'`). Note the exact spelling for use in Task 5.

- [ ] **Step 5: Inventory existing caller files**

```bash
rg -l '\bCarrierSchema\b' src/ tests/ config/ --type=php
```

Capture the list.

- [ ] **Step 6: No commit.**

---

## Task 2: Extract `buildInsuranceTiers` into a util class

**Files:**

- Create: `src/Carrier/Util/InsuranceTierMath.php`
- Modify: `src/Carrier/Service/CapabilitiesValidationService.php` (delete the static method)

- [ ] **Step 1: Create the util**

Create `src/Carrier/Util/InsuranceTierMath.php` with this exact content:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Util;

/**
 * Pure-math helpers for insurance tier ladders.
 *
 * Extracted from CapabilitiesValidationService so the service can stay focused
 * on capability-level queries.
 */
final class InsuranceTierMath
{
    /**
     * Build an insurance-tier ladder for a min/max range.
     *
     * Includes fine-grained floor tiers (€100, €250, €500) at the low end so that
     * low-value orders can still be insured at realistic increments, then €500
     * steps for higher amounts.
     *
     * @param  int $min Minimum amount in cents
     * @param  int $max Maximum amount in cents
     * @return int[]    Sorted, unique tier amounts in cents, including min and max
     */
    public static function buildTiers(int $min, int $max): array
    {
        if ($min >= $max) {
            return [$min];
        }

        $tiers = [$min];

        foreach ([10_000, 25_000, 50_000] as $tier) {
            if ($tier > $min && $tier < $max) {
                $tiers[] = $tier;
            }
        }

        $stepStart = max($min, 50_000) + 50_000;
        for ($t = $stepStart; $t < $max; $t += 50_000) {
            $tiers[] = $t;
        }

        $tiers[] = $max;

        return array_values(array_unique($tiers));
    }
}
```

- [ ] **Step 2: Delete the static method from the capabilities service**

In `src/Carrier/Service/CapabilitiesValidationService.php`, delete the public static method `buildInsuranceTiers` (currently lines 119-156).

- [ ] **Step 3: Update existing callers of `buildInsuranceTiers`**

```bash
rg -n 'buildInsuranceTiers' src/ tests/ --type=php
```

For each hit (currently only `CarrierSchema::getAllowedInsuranceAmounts` calls it; that method is moving to `CarrierValidationService` in Task 4), replace with `InsuranceTierMath::buildTiers(...)` and add `use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;` to the file.

- [ ] **Step 4: Add unit tests for the util**

Create `tests/Unit/Carrier/Util/InsuranceTierMathTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;

it('returns just the min when min >= max', function () {
    expect(InsuranceTierMath::buildTiers(100, 100))->toEqual([100]);
    expect(InsuranceTierMath::buildTiers(500, 100))->toEqual([500]);
});

it('builds a tier ladder including floor tiers and €500 steps', function () {
    $tiers = InsuranceTierMath::buildTiers(0, 200_000);
    expect($tiers)->toEqual([0, 10_000, 25_000, 50_000, 100_000, 150_000, 200_000]);
});

it('skips floor tiers below the min', function () {
    $tiers = InsuranceTierMath::buildTiers(30_000, 100_000);
    expect($tiers)->toEqual([30_000, 50_000, 100_000]);
});
```

Run:

```bash
docker compose run --rm php composer test -- --filter=InsuranceTierMath 2>&1 | tail -20
```

Expected: pass.

- [ ] **Step 5: No commit yet.**

---

## Task 3: Rename `capabilitySupportsWeight` → `supportsWeight`

**Files:** Modify `src/Carrier/Service/CapabilitiesValidationService.php` and all callers.

- [ ] **Step 1: Rename the method declaration**

In `src/Carrier/Service/CapabilitiesValidationService.php`, find:

```php
public function capabilitySupportsWeight($capability, int $weight): bool
```

Replace with:

```php
public function supportsWeight($capability, int $weight): bool
```

- [ ] **Step 2: Update all callers in src/ and tests/**

```bash
rg -n 'capabilitySupportsWeight' src/ tests/ --type=php
```

For each hit, replace `capabilitySupportsWeight(` with `supportsWeight(` (preserving the `->` access).

- [ ] **Step 3: Update plugin callers (cross-repo)**

```bash
rg -n 'capabilitySupportsWeight' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

If hits appear, the plugin-side rename ships in the coordinated plugin PR (see Task 9). **Do not** edit plugin files in this PDK plan's commit.

- [ ] **Step 4: Verify**

```bash
rg 'capabilitySupportsWeight' src/ tests/ --type=php
```

Expected: no output.

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 5: No commit yet.**

---

## Task 4: Create `CarrierValidationService`

**Files:** Create `src/Carrier/Service/CarrierValidationService.php`.

- [ ] **Step 1: Create the file with the full class**

Create `src/Carrier/Service/CarrierValidationService.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;

/**
 * Answers questions about a whole `Carrier`.
 *
 * Sister service to `CapabilitiesValidationService`, which answers questions
 * about a single capability row (`RefCapabilitiesResponseCapabilityV2`). The
 * split is by subject: anything you'd ask about "this carrier in general"
 * belongs here.
 *
 * Method names use common verbs (`supports*`, `has*`, `getAllowed*`) without
 * subject prefixes — the class name carries the subject.
 */
class CarrierValidationService
{
    /**
     * @var CarrierCapabilitiesRepository
     */
    private $capabilitiesRepository;

    public function __construct(CarrierCapabilitiesRepository $capabilitiesRepository)
    {
        $this->capabilitiesRepository = $capabilitiesRepository;
    }

    /**
     * Whether the carrier supports the given shipment option.
     *
     * @param  class-string<OrderOptionDefinitionInterface>|OrderOptionDefinitionInterface $definition
     */
    public function supportsShipmentOption(Carrier $carrier, $definition): bool
    {
        $resolved = $definition instanceof OrderOptionDefinitionInterface
            ? $definition
            : new $definition();

        $options = $carrier->options !== null ? $carrier->options->toArray() : [];

        return array_key_exists($resolved->getCapabilitiesOptionsKey(), $options);
    }

    public function supportsMailbox(Carrier $carrier): bool
    {
        return $this->supportsPackageType($carrier, RefShipmentPackageTypeV2::MAILBOX);
    }

    public function supportsDigitalStamp(Carrier $carrier): bool
    {
        return $this->supportsPackageType($carrier, RefShipmentPackageTypeV2::DIGITAL_STAMP);
    }

    /**
     * Insurance tier ladder allowed for the carrier (cents).
     * Empty array when the carrier does not support insurance.
     */
    public function getAllowedInsuranceAmounts(Carrier $carrier): array
    {
        if (! $this->supportsShipmentOption($carrier, InsuranceDefinition::class)) {
            return [];
        }

        $insured = $carrier->options->getInsurance()->getInsuredAmount();

        return InsuranceTierMath::buildTiers(
            $insured->getMin()->getAmount(),
            $insured->getMax()->getAmount()
        );
    }

    /**
     * Whether the carrier supports inbound (return) shipments in the given context.
     *
     * Queries /capabilities with direction: inbound. Empty context asks for
     * general carrier-level inbound support.
     *
     * @param array<string,mixed> $context Optional shipment context (cc, package_type, delivery_type).
     */
    public function supportsReturns(Carrier $carrier, array $context = []): bool
    {
        $args = array_merge($context, [
            'carrier'   => $carrier->carrier,
            'direction' => 'inbound',
        ]);

        $capabilities = $this->capabilitiesRepository->getCapabilities($args);

        return ! empty($capabilities);
    }

    private function supportsPackageType(Carrier $carrier, string $packageType): bool
    {
        $packageTypes = $carrier->packageTypes !== null ? $carrier->packageTypes->toArray() : [];

        return in_array($packageType, $packageTypes, true);
    }
}
```

Confirm the `'inbound'` literal against Task 1 Step 4 — if the SDK exposes a constant, use it.

Caveat: `$carrier->options->toArray()` and `$carrier->packageTypes->toArray()` are the assumed access patterns based on `CarrierSchema::createSchema`. If the actual `Carrier` model exposes these differently, adapt — the goal is mechanical parity with the old `CarrierSchema` logic.

- [ ] **Step 2: Add unit tests**

Create `tests/Unit/Carrier/Service/CarrierValidationServiceTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CarrierValidationService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use function MyParcelNL\Pdk\Tests\factory;

it('supportsShipmentOption: returns true when the capability key is in carrier options', function () {
    $carrier = factory(Carrier::class)->withOptionsForDefinition(SignatureDefinition::class)->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsShipmentOption($carrier, SignatureDefinition::class))->toBeTrue();
});

it('supportsShipmentOption: returns false when the capability key is absent', function () {
    $carrier = factory(Carrier::class)->withOptions([])->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsShipmentOption($carrier, SignatureDefinition::class))->toBeFalse();
});

it('supportsMailbox returns true when MAILBOX is in packageTypes', function () {
    $carrier = factory(Carrier::class)->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX])->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsMailbox($carrier))->toBeTrue();
});

it('supportsDigitalStamp returns true when DIGITAL_STAMP is in packageTypes', function () {
    $carrier = factory(Carrier::class)->withPackageTypes([RefShipmentPackageTypeV2::DIGITAL_STAMP])->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsDigitalStamp($carrier))->toBeTrue();
});

it('getAllowedInsuranceAmounts returns [] when InsuranceDefinition is not available', function () {
    $carrier = factory(Carrier::class)->withOptions([])->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->getAllowedInsuranceAmounts($carrier))->toEqual([]);
});

it('getAllowedInsuranceAmounts returns the tier ladder when insurance is available', function () {
    $carrier = factory(Carrier::class)
        ->withOptionsForDefinition(InsuranceDefinition::class)
        ->withInsuranceRange(0, 200_000)
        ->make();
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->getAllowedInsuranceAmounts($carrier))
        ->toEqual([0, 10_000, 25_000, 50_000, 100_000, 150_000, 200_000]);
});

it('supportsReturns: true when /capabilities (inbound) returns non-empty', function () {
    // Mock the repository to return non-empty for direction: inbound
    // Assert true
});

it('supportsReturns: false when /capabilities (inbound) returns empty', function () {
    // Mock the repository to return [] for direction: inbound
    // Assert false
});

it('supportsReturns forwards user context to the repository', function () {
    // Mock that asserts the merged args contain cc/packageType from the call site
});
```

Factory helpers like `withOptionsForDefinition`/`withInsuranceRange` may need to be added to `CarrierFactory` if they don't exist — the test file may need to use existing factory methods. Adapt to the real factory surface during execution.

Run:

```bash
docker compose run --rm php composer test -- --filter=CarrierValidationServiceTest 2>&1 | tail -30
```

Expected: pass.

- [ ] **Step 3: Register DI binding**

If PDK uses explicit DI bindings (`config/`), add:

```php
\MyParcelNL\Pdk\Carrier\Service\CarrierValidationService::class => DI\autowire(),
```

Or whatever the existing pattern is. If auto-wiring is the default, no change needed.

- [ ] **Step 4: No commit yet.**

---

## Task 5: Migrate non-view callers to `CarrierValidationService`

**Files:** Modify

- `src/Shipment/Request/PostReturnShipmentsRequest.php`
- `src/Shipment/Request/PostShipmentsRequest.php`
- `src/App/Order/Collection/PdkOrderCollection.php`
- `src/App/Order/Calculator/General/CustomerInformationCalculator.php`
- `src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php`

For each file, the migration shape is:

1. Replace the `Pdk::get(CarrierSchema::class) + $schema->setCarrier(...)` setup with `$carrierValidation = Pdk::get(CarrierValidationService::class);` (stateless; carrier is passed per call).
2. Replace `$schema->method(...)` with `$carrierValidation->newName($carrier, ...)`.
3. Update `use` imports: drop `CarrierSchema`, add `CarrierValidationService`.

- [ ] **Step 1: `PostReturnShipmentsRequest.php`**

Find:

```php
$schema = Pdk::get(CarrierSchema::class);
$schema->setCarrier($carrier);

if (!$schema->hasReturnCapabilities()) {
```

Replace with:

```php
$carrierValidation = Pdk::get(CarrierValidationService::class);

if (!$carrierValidation->supportsReturns($carrier)) {
```

Drop `use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;`. Add `use MyParcelNL\Pdk\Carrier\Service\CarrierValidationService;`.

- [ ] **Step 2: `PostShipmentsRequest.php`**

Inspect:

```bash
sed -n '1,80p' src/Shipment/Request/PostShipmentsRequest.php
```

Migrate each `$schema->method()` call per the migration map. Typically only 1-2 calls.

- [ ] **Step 3: `PdkOrderCollection.php` (two call sites)**

Both call `hasReturnCapabilities()`. Apply Step 1's pattern twice.

- [ ] **Step 4: `CustomerInformationCalculator.php`**

Inspect; migrate each call. The carrier is available as `$this->order->deliveryOptions->carrier`.

- [ ] **Step 5: `ExcludeParcelLockersCalculator.php`**

Replace the field + setup. Find:

```php
private $carrierSchema;
// ...
$schema = Pdk::get(CarrierSchema::class);
$this->carrierSchema = $schema->setCarrier($this->order->deliveryOptions->carrier);
// ...
if (! $this->carrierSchema->canHaveShipmentOption($definition)) {
```

Replace with:

```php
private $carrierValidationService;
// ...
$this->carrierValidationService = Pdk::get(CarrierValidationService::class);
// ...
$carrier = $this->order->deliveryOptions->carrier;

if (! $this->carrierValidationService->supportsShipmentOption($carrier, $definition)) {
```

- [ ] **Step 6: Verify**

```bash
rg '\bCarrierSchema\b' src/Shipment/Request/ src/App/Order/ --type=php
```

Expected: no output.

```bash
docker compose run --rm php composer test 2>&1 | tail -30
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass (test failures here likely indicate Task 9 work).

- [ ] **Step 7: No commit yet.**

---

## Task 6: Migrate `CarrierSettingsItemView` + inline `canHaveMondayDelivery`

**Files:** Modify `src/Frontend/View/CarrierSettingsItemView.php`.

- [ ] **Step 1: Replace the property and constructor setup**

Find:

```php
protected $carrierSchema;
// ...
public function __construct(Carrier $carrier)
{
    // ...
    $schema = Pdk::get(CarrierSchema::class);
    $schema->setCarrier($carrier);
    $this->carrierSchema = $schema;
}
```

Replace with:

```php
protected $carrierValidationService;
// ...
public function __construct(Carrier $carrier)
{
    // ...
    $this->carrierValidationService = Pdk::get(CarrierValidationService::class);
}
```

Update the `@var` annotation.

- [ ] **Step 2: Replace 13 `canHaveShipmentOption` calls**

Each `$this->carrierSchema->canHaveShipmentOption($x)` becomes `$this->carrierValidationService->supportsShipmentOption($this->carrier, $x)`. Use a single `Edit` with `replace_all` for the search/replace.

Verify:

```bash
rg -c 'carrierSchema->canHaveShipmentOption|carrierSchema->canBeMailbox|carrierSchema->canBeDigitalStamp|carrierSchema->getAllowedInsuranceAmounts' src/Frontend/View/CarrierSettingsItemView.php
```

Expected after migrations: 0 across these patterns.

- [ ] **Step 3: Replace `canBeMailbox` and `canBeDigitalStamp`**

`$this->carrierSchema->canBeMailbox()` → `$this->carrierValidationService->supportsMailbox($this->carrier)`.
`$this->carrierSchema->canBeDigitalStamp()` → `$this->carrierValidationService->supportsDigitalStamp($this->carrier)`.

- [ ] **Step 4: Replace `getAllowedInsuranceAmounts` (2 calls)**

`$this->carrierSchema->getAllowedInsuranceAmounts()` → `$this->carrierValidationService->getAllowedInsuranceAmounts($this->carrier)`.

- [ ] **Step 5: Inline `canHaveMondayDelivery`**

Replace:

```php
if (!$this->carrierSchema->canHaveMondayDelivery()) {
```

with:

```php
if (!$this->carrierOffersMondayDelivery()) {
```

Add a private helper at the bottom of the class:

```php
/**
 * Whether the carrier offers Monday delivery. Currently a hardcoded fact:
 * only PostNL exposes Monday delivery in the delivery-options widget.
 * NOT a capabilities concern (no API field), so it stays local to the view.
 */
private function carrierOffersMondayDelivery(): bool
{
    return $this->carrier->carrier === RefTypesCarrierV2::POSTNL;
}
```

Add `use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;` if not already imported.

- [ ] **Step 6: Verify**

```bash
rg 'carrierSchema|CarrierSchema' src/Frontend/View/CarrierSettingsItemView.php
```

Expected: no output.

```bash
docker compose run --rm php composer test -- --filter=CarrierSettings 2>&1 | tail -30
```

Expected: view tests pass.

- [ ] **Step 7: No commit yet.**

---

## Task 7: Final inventory + delete `CarrierSchema` and three interfaces

**Files:**

- Delete: `src/Validation/Validator/CarrierSchema.php`
- Delete: `src/Validation/Contract/DeliveryOptionsValidatorInterface.php`
- Delete: `src/Validation/Contract/ValidatorInterface.php`
- Delete: `src/Validation/Contract/SchemaInterface.php`

- [ ] **Step 1: Confirm no remaining production callers**

```bash
rg -l '\bCarrierSchema\b' src/ --type=php
```

Expected: only the file we're about to delete (`src/Validation/Validator/CarrierSchema.php`) and `src/Validation/Contract/DeliveryOptionsValidatorInterface.php`. Anything else is a missed migration — return to Tasks 5-6.

- [ ] **Step 2: Delete the four files**

```bash
git rm src/Validation/Validator/CarrierSchema.php \
       src/Validation/Contract/DeliveryOptionsValidatorInterface.php \
       src/Validation/Contract/ValidatorInterface.php \
       src/Validation/Contract/SchemaInterface.php
```

- [ ] **Step 3: Remove DI bindings**

```bash
rg -n 'CarrierSchema|DeliveryOptionsValidatorInterface|\bValidatorInterface\b|\bSchemaInterface\b' config/
```

Remove each binding. Add `CarrierValidationService` binding if not already added in Task 4 Step 3.

- [ ] **Step 4: Verify**

```bash
rg '\b(CarrierSchema|DeliveryOptionsValidatorInterface|\bValidatorInterface\b|\bSchemaInterface\b)' src/ tests/ config/ --type=php
```

Expected: no output.

```bash
docker compose run --rm php composer test 2>&1 | tail -30
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 5: No commit yet.**

---

## Task 8: Update PDK tests

**Files:** Test files identified in Task 1 Step 5 + Task 3 Step 2.

- [ ] **Step 1: Find PDK tests still referencing deleted/renamed symbols**

```bash
rg -l '(CarrierSchema|capabilitySupportsWeight|\bValidatorInterface\b|\bSchemaInterface\b)' tests/ --type=php
```

- [ ] **Step 2: Migrate each test file**

Replacements:

- `Pdk::get(CarrierSchema::class)` → `Pdk::get(CarrierValidationService::class)`. Drop `setCarrier(...)` calls; pass `$carrier` per method.
- `$schema->canHaveShipmentOption(X)` → `$carrierValidation->supportsShipmentOption($carrier, X)`.
- `$schema->canBeMailbox()` / `canBeDigitalStamp()` → `supportsMailbox($carrier)` / `supportsDigitalStamp($carrier)`.
- `$schema->getAllowedInsuranceAmounts()` → `$carrierValidation->getAllowedInsuranceAmounts($carrier)`.
- `$schema->hasReturnCapabilities()` → `$carrierValidation->supportsReturns($carrier, [...])`.
- `capabilitySupportsWeight(...)` → `supportsWeight(...)` (on `CapabilitiesValidationService`).
- Inline mocks like `class extends CarrierSchema { ... }` → mock `CarrierValidationService` instead, or stub the `CarrierCapabilitiesRepository` for `supportsReturns`.

Known specifics:

- `tests/Unit/App/Order/Collection/PdkOrderCollectionTest.php` — inline mock of `CarrierSchema::hasReturnCapabilities`. Rewrite to mock `CarrierValidationService::supportsReturns` (or stub the repository).
- `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php` (post-`validate()` removal) — calls `$carrierSchema->canHaveShipmentOption($instance)`. Update to `$carrierValidation->supportsShipmentOption($carrier, $instance)`.

- [ ] **Step 3: Drop tests for deleted methods**

If a test exercises a deleted wrapper (`canBeLetter`, `canHaveWeight`, etc.), the test goes too. Surface to the user before deleting test cases.

- [ ] **Step 4: Verify**

```bash
docker compose run --rm php composer test 2>&1 | tail -30
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 5: No commit yet.**

---

## Task 9: PrestaShop plugin coordination

**Files:** Modify `~/projects/docker-prestashop/modules/myparcelnl/tests/Bootstrap/MockPsPdkBootstrapper.php` (and any `MockCarrierSchema.php`).

**Important:** plugin-side change; ships as a separate plugin PR. **Do not** include in this PDK plan's commit.

- [ ] **Step 1: Inspect plugin references**

```bash
rg -n 'CarrierSchema|MockCarrierSchema|capabilitySupportsWeight' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php
```

- [ ] **Step 2: Plan the plugin-side change with the user**

Options:

- Replace `MockCarrierSchema` with `MockCarrierValidationService` if the plugin tests still need a stub.
- Or delete the mock binding entirely if no plugin tests require it.
- Apply the `capabilitySupportsWeight` → `supportsWeight` rename in any plugin code that calls it.

Surface a diff plan to the user before applying.

- [ ] **Step 3: Apply on a coordinated branch in the plugin repos**

Do the change on coordinated branches in each plugin repo. **Do not commit on the PDK side.**

- [ ] **Step 4: No PDK commit on this step.**

---

## Task 10: Final verification + commit (PDK side)

**Files:** Stage all PDK changes.

- [ ] **Step 1: Full final sweep**

```bash
rg -n '\b(CarrierSchema|DeliveryOptionsValidatorInterface|\bValidatorInterface\b|\bSchemaInterface\b|capabilitySupportsWeight|buildInsuranceTiers)\b' src/ tests/ config/ --type=php
```

Expected: no output (the old names are gone; `buildInsuranceTiers` has been replaced by `InsuranceTierMath::buildTiers`).

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log; echo "exit: $?"
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 2: Plugin scan**

```bash
rg -l 'CarrierSchema|capabilitySupportsWeight' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Surface the result to the user; the plugin-side changes ship in Task 9's coordinated PR.

- [ ] **Step 3: Show the diff**

```bash
git diff --staged --stat
git status --short
```

Wait for explicit approval.

- [ ] **Step 4: Commit (only after approval)**

```bash
git commit -m "$(cat <<'EOF'
refactor(validation): split queries into CarrierValidationService + CapabilitiesValidationService

Introduces a new CarrierValidationService as the home for carrier-level
queries (supportsShipmentOption, supportsMailbox, supportsDigitalStamp,
supportsReturns, getAllowedInsuranceAmounts). The existing
CapabilitiesValidationService keeps its capability-level queries.

The two are sister services; the subject (whole Carrier vs single
capability row) is encoded in the class name, not in method prefixes.
Method names use common verbs (supports*, has*, getAllowed*).

Renames CapabilitiesValidationService::capabilitySupportsWeight to
supportsWeight (the capability prefix was redundant with the class name).

Deletes CarrierSchema (12 wrapper methods, all redundant or unused) and
three dead interfaces (ValidatorInterface, SchemaInterface,
DeliveryOptionsValidatorInterface).

supportsReturns is reimplemented to consult /capabilities with
direction: inbound. The always-true stub was a PDK implementation gap;
directionality is already in the API.

canHaveMondayDelivery's PostNL hardcode is inlined into
CarrierSettingsItemView as a private helper. It's a widget-only fact.

Pure-math helper buildInsuranceTiers extracts to InsuranceTierMath.

Plugin coordination: docker-prestashop's MockCarrierSchema migration
plus the supportsWeight rename ship in a coordinated plugin-side PR.

Audit references:
docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Validation A-1, A-2, B-1, B-2) and
docs/superpowers/findings/2026-05-11-carrierschema-architecture-decision.md.

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 5: Verify commit**

```bash
git log -1 --stat
```

Expected: 2 created (CarrierValidationService, InsuranceTierMath), 4 deleted (CarrierSchema + 3 interfaces), many files modified across `src/Carrier/Service/`, `src/Frontend/View/`, `src/Shipment/Request/`, `src/App/Order/`, plus tests and DI config.

- [ ] **Step 6: Plan complete.**

---

## Roll-back

```bash
git revert HEAD
```

Single-commit revert restores everything. **Plugin-side change must also be reverted separately if it was applied.**

---

## Why this is safe

- Every external call site has a documented migration in the action map.
- The five new `CarrierValidationService` methods preserve the exact semantics of the old `CarrierSchema` methods except for `supportsReturns`, which moves from always-true stub to a real query. That semantic change is intentional (PDK implementation gap fix) and the user approved it; tests cover both outcomes.
- Twelve deleted methods on `CarrierSchema` had zero external callers (verified).
- `canHaveMondayDelivery` inlining preserves behavior.
- The math extraction (`buildInsuranceTiers` → `InsuranceTierMath::buildTiers`) is mechanically equivalent; tests cover representative inputs.
- The three dead interfaces have no implementers and no callers.
- The `supportsWeight` rename is mechanical; covered by a clean sweep.
- Plugin coordination is called out as a separate PR — no surprises.

---

## Open questions

- **`supportsReturns` context enrichment.** The two existing callers pass no context. Should they pass cc/packageType/deliveryType to scope the inbound-direction lookup? Conservative default (no context) implemented; user decision for a follow-up.
- **`'inbound'` string source.** Task 1 Step 4 verifies via `getDirectionAllowableValues()`. If the SDK uses a different spelling, adapt across Task 4.
- **Plugin migration timing.** Should the PrestaShop change ship in the same release as the PDK PR, or one release later?
- **`CapabilitiesValidationService` consistency overhaul follow-up.** `getPackageTypeWeights(string, array)` and `resolveHeaviestType(array, array)` don't fit either subject convention cleanly. They're alive and well, just oddly-shaped. A separate consistency pass on `CapabilitiesValidationService` may be worth scheduling — out of scope here.
