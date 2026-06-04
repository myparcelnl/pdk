# Determine Carrier and Package Type Availability from API in Checkout

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace local JSON schema-based carrier filtering in the consumer checkout with the contextual capabilities API, so the available carriers, package types, and weight limits are determined at runtime from the API instead of hardcoded schema files. Propagate the contract ID from the capabilities response onto delivery options and shipments.

**Architecture:** A new `getCapabilitiesForRecipient()` method on `CarrierCapabilitiesRepository` calls the capabilities API once per recipient country and caches the response. `DeliveryOptionsService` uses this instead of `SchemaRepository` to filter carriers by package type and weight. The `DeliveryOptions` model gains a `contractId` attribute that propagates to `Shipment.contractId` during export.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models (`RefCapabilitiesResponseCapabilityV2`, `RefCapabilitiesSharedContractV2`), Guzzle MockHandler for API mocking

**Jira:** [INT-1500](https://myparcelnl.atlassian.net/browse/INT-1500) (sub-task of INT-930)

**Branch:** `feat/INT-1500-checkout-capabilities-api`

**Related plans:**

- Plan 2 (future): Replace carrier-specific calculators with capabilities `requires`/`excludes`
- Plan 3 (future): Replace insurance tier schema lookups (INT-930 remainder)
- Plan 4 (future): Dynamic settings + constant cleanup

---

## Design decision: broad vs specific capabilities call

The capabilities API accepts optional filters like `physical_properties.weight`, `package_type`, and `carrier`. We intentionally call it with **only** `{ recipient: { cc } }` for the checkout context and filter weight/package type locally.

**Why:** The checkout is a hot path — many visitors, varying cart weights. A broad call cached per country is reused across all carts. Adding weight to the call would create a cache key per country+weight combination, busting the cache on nearly every render. Local weight filtering is trivial (two comparisons against min/max from the response).

For order export (Plan 2), calling with full context (carrier, recipient, package_type, weight, options) is appropriate — that's a one-shot call per export, not a hot checkout path.

This reasoning should be documented in code comments where the API call is made and where local filtering happens.

---

## TODOs resolved by this plan

| File                                | Line | TODO                                                        | How resolved                                                     |
| ----------------------------------- | ---- | ----------------------------------------------------------- | ---------------------------------------------------------------- |
| `CarrierCapabilitiesRepository.php` | 59   | "This is just a placeholder till we implement capabilities" | Task 1: proper implementation + `getCapabilitiesForRecipient()`  |
| `DeliveryOptionsService.php`        | 289  | "TODO: support full package type class instead of string"   | Task 5: entire schema call removed, replaced by capabilities API |

### TODOs NOT in scope (documented for follow-up plans)

| File                      | Line         | TODO                                                                      | Plan   |
| ------------------------- | ------------ | ------------------------------------------------------------------------- | ------ |
| `CarrierSchema.php`       | 155          | "Replace by a directionality call to capabilities given shipment context" | Plan 2 |
| `InsuranceCalculator.php` | 25, 204, 215 | "INT-930: replace schema-based tier deviations with capabilities API"     | Plan 3 |

---

## Behavioral test matrix

A tester should verify these checkout scenarios behave identically before and after this change. **End-user behavior should not change** — the same carriers appear for the same cart contents. The only new behavior is that the API's contract ID is now attached to shipments.

Best tested in **PrestaShop** — it shows carriers before handing off to the delivery options widget, which may filter carriers itself.

### Existing behavior (must not regress)

| #   | Scenario                                                   | Expected                                                                               |
| --- | ---------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| 1   | Cart with standard package, NL address                     | All enabled carriers that support PACKAGE for NL are shown                             |
| 2   | Cart with mailbox-eligible items, NL address               | Only carriers that support MAILBOX for NL are shown                                    |
| 3   | Cart with digital stamp items, NL address                  | Only carriers that support DIGITAL_STAMP for NL are shown                              |
| 4   | Cart weight exceeds carrier max (e.g. 25kg PostNL package) | That carrier is hidden from checkout                                                   |
| 5   | Cart weight below carrier minimum (e.g. <100g GLS)         | That carrier is hidden from checkout                                                   |
| 6   | International address (e.g. BE, DE)                        | Only carriers available for that destination shown; package types filtered per country |
| 7   | Carrier with delivery options disabled in settings         | Always hidden regardless of capabilities                                               |
| 8   | No carriers match for the given package type/weight        | Falls back to default package type, shows all enabled carriers                         |
| 9   | Mixed cart (some items mailbox, some package)              | Largest package type wins; carriers filtered accordingly                               |

### New behavior

| #   | Scenario                         | Expected                                                      |
| --- | -------------------------------- | ------------------------------------------------------------- |
| 10  | Contract ID on exported shipment | Shipment export request includes the API-resolved contract ID |

---

## Context for the implementing agent

### What the capabilities API returns

`POST /shipments/capabilities` accepts optional filters (`carrier`, `recipient`, `package_type`, `physical_properties`, `delivery_type`, `options`, `direction`) and returns an array of `RefCapabilitiesResponseCapabilityV2` items. Only `recipient` is required.

Each item contains:

- `carrier` — V2 carrier name (e.g. `POSTNL`)
- `contract` — `{ id: int, type: string }` — the contract this capability applies to
- `packageTypes` — available package types for this context
- `options` — available shipment options, each with `isRequired`, `isSelectedByDefault`, `requires[]`, `excludes[]`
- `physicalProperties` — `weight`, `width`, `length`, `height` etc., each with `min`/`max`
- `deliveryTypes` — available delivery types for this context
- `collo` — `{ max: int }`

### What we're replacing

`DeliveryOptionsService.getValidCarrierOptions()` currently:

1. Iterates all carriers from `CarrierRepository`
2. For each carrier, calls `SchemaRepository.getOrderValidationSchema(carrier, cc, packageType)`
3. Validates package type and weight against the schema via `SchemaRepository.validateOption()`
4. Returns the first valid package type + filtered carrier list

After this plan, it will:

1. Call the capabilities API once with `{ recipient: { cc } }` (all carriers returned)
2. Filter results by enabled carriers and package type availability
3. Validate weight against `physicalProperties.weight.min/max` from the response
4. Return the first valid package type + filtered carrier list, with contract IDs attached

### Key files to understand before starting

| File                                                                     | Why                                                                         |
| ------------------------------------------------------------------------ | --------------------------------------------------------------------------- |
| `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`             | Main file being modified — checkout carrier filtering                       |
| `src/Carrier/Repository/CarrierCapabilitiesRepository.php`               | Caching wrapper around API — `getCapabilities()` is currently a placeholder |
| `src/SdkApi/Service/CoreApi/Shipment/CapabilitiesService.php`            | Low-level API client — already works, no changes needed                     |
| `src/Shipment/Model/DeliveryOptions.php`                                 | Gains `contractId` attribute                                                |
| `src/Shipment/Model/Shipment.php`                                        | Already has `contractId` — needs auto-population from delivery options      |
| `src/Shipment/Request/PostShipmentsRequest.php`                          | Already sends `contract_id` — no changes needed                             |
| `tests/factories/Carrier/Model/CarrierFactory.php`                       | Test factory patterns for carrier capabilities                              |
| `tests/Unit/SdkApi/Service/CoreApi/Shipment/CapabilitiesServiceTest.php` | Existing API test patterns with MockHandler                                 |

### Test infrastructure

- Tests run via Docker: `yarn run test` or `docker compose run php composer test -- --filter="test name"`
- Use `MockHandler` + `MockableCapabilitiesService` pattern from `CapabilitiesServiceTest.php` for API mocking
- Use `factory(Carrier::class)->withAllCapabilities()->store()` for carrier setup
- Use `TestBootstrapper::hasApiKey()` for API auth
- Pest v1 only — no `describe()` blocks, no `arch()`, no `covers()`

---

## File Structure

| File                                                                                | Action | Responsibility                                                                                       |
| ----------------------------------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------- |
| `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`                        | Modify | Replace `SchemaRepository` with `CarrierCapabilitiesRepository` for checkout filtering               |
| `src/Carrier/Repository/CarrierCapabilitiesRepository.php`                          | Modify | Implement `getCapabilities()` properly, add `getCapabilitiesForRecipient()`, remove placeholder TODO |
| `src/Carrier/Model/Carrier.php`                                                     | Modify | Add transient `$contractId` property                                                                 |
| `src/Shipment/Model/DeliveryOptions.php`                                            | Modify | Add `contractId` attribute                                                                           |
| `src/App/Order/Model/PdkOrder.php`                                                  | Modify | Propagate `contractId` from delivery options to shipment in `createShipment()`                       |
| `tests/Unit/App/DeliveryOptions/Service/DeliveryOptionsServiceCapabilitiesTest.php` | Create | Tests for capabilities-based carrier filtering                                                       |
| `tests/Unit/Carrier/Repository/CarrierCapabilitiesRepositoryTest.php`               | Create | Tests for the real `getCapabilities()` implementation                                                |
| `tests/Unit/Shipment/Model/DeliveryOptionsContractIdTest.php`                       | Create | Tests for contract ID on delivery options                                                            |
| `tests/Unit/App/Order/Model/PdkOrderContractIdTest.php`                             | Create | Tests for contract ID propagation to shipment                                                        |
| `tests/Unit/Carrier/Model/CarrierContractIdTest.php`                                | Create | Tests for transient contractId on Carrier                                                            |

---

### Task 1: Implement `getCapabilitiesForRecipient()` on `CarrierCapabilitiesRepository`

The existing `getCapabilities()` is a placeholder (see TODO at line 59). We need a proper implementation plus a convenience method optimized for the checkout use case (fetch all carriers for a recipient country).

**Files:**

- Modify: `src/Carrier/Repository/CarrierCapabilitiesRepository.php`
- Create: `tests/Unit/Carrier/Repository/CarrierCapabilitiesRepositoryTest.php`

- [ ] **Step 1: Write failing test for `getCapabilitiesForRecipient()`**

```php
<?php
// tests/Unit/Carrier/Repository/CarrierCapabilitiesRepositoryTest.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\MockableCapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns capabilities for a given recipient country code', function () {
    TestBootstrapper::hasApiKey('test-key');

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [
            [
                'carrier'             => 'POSTNL',
                'contract'            => ['id' => 123, 'type' => 'main'],
                'package_types'       => ['PACKAGE', 'MAILBOX'],
                'options'             => (object) [],
                'physical_properties' => [
                    'weight' => ['min' => 1, 'max' => 23000],
                ],
                'delivery_types'      => ['STANDARD_DELIVERY', 'PICKUP_DELIVERY'],
                'transaction_types'   => [],
                'collo'               => ['max' => 1],
            ],
        ],
    ])));

    $repository = new CarrierCapabilitiesRepository(
        Pdk::get(StorageInterface::class),
        $mockService
    );

    $result = $repository->getCapabilitiesForRecipient('NL');

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]->getCarrier())->toBe('POSTNL')
        ->and($result[0]->getContract()->getId())->toBe(123)
        ->and($result[0]->getPackageTypes())->toContain('PACKAGE', 'MAILBOX');
});

it('caches capabilities by recipient country code', function () {
    TestBootstrapper::hasApiKey('test-key');

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [
            [
                'carrier'             => 'POSTNL',
                'contract'            => ['id' => 1, 'type' => 'main'],
                'package_types'       => ['PACKAGE'],
                'options'             => (object) [],
                'physical_properties' => ['weight' => ['min' => 1, 'max' => 23000]],
                'delivery_types'      => ['STANDARD_DELIVERY'],
                'transaction_types'   => [],
                'collo'               => ['max' => 1],
            ],
        ],
    ])));

    $repository = new CarrierCapabilitiesRepository(
        Pdk::get(StorageInterface::class),
        $mockService
    );

    $first  = $repository->getCapabilitiesForRecipient('NL');
    $second = $repository->getCapabilitiesForRecipient('NL');

    expect($first)->toBe($second)
        ->and($mockService->capturedRequests)->toHaveCount(1);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="returns capabilities for a given recipient"`
Expected: FAIL — method `getCapabilitiesForRecipient` does not exist

- [ ] **Step 3: Implement `getCapabilitiesForRecipient()`, remove placeholder TODO**

Replace the entire file with a clean implementation. The placeholder `@TODO` comment on `getCapabilities()` (line 59) is resolved by this implementation.

```php
<?php
// src/Carrier/Repository/CarrierCapabilitiesRepository.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;

/**
 * Caching wrapper around the CoreApi CapabilitiesService.
 */
class CarrierCapabilitiesRepository extends Repository
{
    /**
     * @var \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService
     */
    protected $apiService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                   $storage
     * @param  \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $apiService
     */
    public function __construct(StorageInterface $storage, CapabilitiesService $apiService)
    {
        parent::__construct($storage);

        $this->apiService = $apiService;
    }

    /**
     * Return contract definitions from the API as a CarrierCollection of Carrier models.
     *
     * @param  null|string $carrier Carrier name in v2 format (eg. "POSTNL")
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getContractDefinitions(?string $carrier = null): CarrierCollection
    {
        $cacheKey = "contractDefinitions.$carrier";

        return $this->retrieve($cacheKey, function () use ($carrier) {
            $contractDefinitions = $this->apiService->getContractDefinitions($carrier);
            $contractDefinitions = array_map(function ($contractDefinition) {
                return json_decode(json_encode($contractDefinition->jsonSerialize()), true);
            }, $contractDefinitions);

            return new CarrierCollection($contractDefinitions);
        });
    }

    /**
     * Get contextual capabilities for a specific set of parameters.
     *
     * @param  array $args Request parameters (carrier, recipient, package_type, etc.)
     *
     * @return RefCapabilitiesResponseCapabilityV2[]
     */
    public function getCapabilities(array $args): array
    {
        $cacheKey = 'capabilities.' . md5(json_encode($args));

        return $this->retrieve($cacheKey, function () use ($args) {
            return $this->apiService->getCapabilities($args);
        });
    }

    /**
     * Get all available capabilities for a recipient country.
     *
     * Optimized for the checkout use case: one API call returns capabilities for all
     * carriers available for the given destination. Results are cached per country code.
     *
     * We intentionally pass only the recipient country — not weight, package type, or
     * carrier — so the result is reusable across all carts shipping to the same country.
     * The checkout is a hot path with varying cart contents; adding weight to the call
     * would create a cache key per country+weight combination, busting the cache on
     * nearly every render. Weight and package type are filtered locally instead.
     *
     * For order export (non-checkout) use getCapabilities() with full shipment context.
     *
     * @param  string $cc ISO 3166-1 alpha-2 country code
     *
     * @return RefCapabilitiesResponseCapabilityV2[]
     */
    public function getCapabilitiesForRecipient(string $cc): array
    {
        return $this->getCapabilities([
            'recipient' => ['cc' => $cc],
        ]);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `docker compose run php composer test -- --filter="CarrierCapabilitiesRepositoryTest"`
Expected: All tests PASS

- [ ] **Step 5: Commit**

```bash
git add src/Carrier/Repository/CarrierCapabilitiesRepository.php tests/Unit/Carrier/Repository/CarrierCapabilitiesRepositoryTest.php
git commit -m "feat(capabilities): add getCapabilitiesForRecipient to CarrierCapabilitiesRepository"
```

---

### Task 2: Add transient `contractId` to `Carrier` model

The `Carrier` model needs to temporarily hold the contract ID from the capabilities response so `DeliveryOptionsService` can pass it downstream. This is NOT persisted or serialized — it's set at runtime when capabilities are resolved.

**Files:**

- Modify: `src/Carrier/Model/Carrier.php`
- Create: `tests/Unit/Carrier/Model/CarrierContractIdTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/Carrier/Model/CarrierContractIdTest.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('allows setting and getting contractId as a transient property', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    $carrier->contractId = 42;

    expect($carrier->contractId)->toBe(42);
});

it('defaults contractId to null', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    expect($carrier->contractId)->toBeNull();
});

it('does not include contractId in toArray output', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    $carrier->contractId = 42;

    expect($carrier->toArray())->not->toHaveKey('contractId');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="CarrierContractIdTest"`
Expected: FAIL

- [ ] **Step 3: Add `contractId` as a public property on Carrier**

In `src/Carrier/Model/Carrier.php`, add a public property outside the attribute system so it doesn't interfere with the SDK-backed model serialization:

```php
/**
 * Transient contract ID from capabilities response. Not persisted — set at runtime
 * when capabilities are resolved, so it can propagate to delivery options and shipments.
 *
 * @var int|null
 */
public $contractId;
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `docker compose run php composer test -- --filter="CarrierContractIdTest"`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Carrier/Model/Carrier.php tests/Unit/Carrier/Model/CarrierContractIdTest.php
git commit -m "feat(carrier): add transient contractId for capabilities context"
```

---

### Task 3: Add `contractId` to `DeliveryOptions` model

**Files:**

- Modify: `src/Shipment/Model/DeliveryOptions.php`
- Create: `tests/Unit/Shipment/Model/DeliveryOptionsContractIdTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/Shipment/Model/DeliveryOptionsContractIdTest.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('stores contractId when provided', function () {
    $deliveryOptions = new DeliveryOptions(['contractId' => 123]);
    expect($deliveryOptions->contractId)->toBe(123);
});

it('defaults contractId to null', function () {
    expect((new DeliveryOptions())->contractId)->toBeNull();
});

it('includes contractId in toArray output', function () {
    $do = new DeliveryOptions(['contractId' => 456]);
    expect($do->toArray())->toHaveKey('contractId', 456);
});

it('includes contractId in toStorableArray output', function () {
    $do = new DeliveryOptions(['contractId' => 789]);
    expect($do->toStorableArray())->toHaveKey('contractId', 789);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="DeliveryOptionsContractIdTest"`
Expected: FAIL

- [ ] **Step 3: Add contractId attribute to DeliveryOptions**

In `src/Shipment/Model/DeliveryOptions.php`, add phpdoc `@property null|int $contractId`, constant `CONTRACT_ID = 'contractId'`, attribute default `null`, and cast `'int'`.

- [ ] **Step 4: Run tests to verify they pass**

Run: `docker compose run php composer test -- --filter="DeliveryOptionsContractIdTest"`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Shipment/Model/DeliveryOptions.php tests/Unit/Shipment/Model/DeliveryOptionsContractIdTest.php
git commit -m "feat(delivery-options): add contractId attribute"
```

---

### Task 4: Propagate contractId from DeliveryOptions to Shipment

**Files:**

- Modify: `src/App/Order/Model/PdkOrder.php`
- Create: `tests/Unit/App/Order/Model/PdkOrderContractIdTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/App/Order/Model/PdkOrderContractIdTest.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('propagates contractId from delivery options to created shipment', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'externalIdentifier' => 'test-123',
        'deliveryOptions'    => [
            'carrier'    => RefCapabilitiesSharedCarrierV2::POSTNL,
            'contractId' => 42,
        ],
        'recipient' => [
            'cc' => 'NL', 'postalCode' => '2132WT', 'city' => 'Hoofddorp',
            'person' => 'Test', 'street' => 'Teststraat', 'number' => '1',
        ],
    ]);

    expect($order->createShipment()->contractId)->toBe('42');
});

it('leaves contractId null on shipment when delivery options has no contractId', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'externalIdentifier' => 'test-456',
        'deliveryOptions'    => ['carrier' => RefCapabilitiesSharedCarrierV2::POSTNL],
        'recipient' => [
            'cc' => 'NL', 'postalCode' => '2132WT', 'city' => 'Hoofddorp',
            'person' => 'Test', 'street' => 'Teststraat', 'number' => '1',
        ],
    ]);

    expect($order->createShipment()->contractId)->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="PdkOrderContractIdTest"`
Expected: FAIL

- [ ] **Step 3: Add `contractId` to Shipment constructor in `PdkOrder::createShipment()`**

In `src/App/Order/Model/PdkOrder.php`, find `createShipment()` and add `'contractId' => $this->deliveryOptions->contractId` to the Shipment constructor array.

- [ ] **Step 4: Run tests to verify they pass**

Run: `docker compose run php composer test -- --filter="PdkOrderContractIdTest"`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/App/Order/Model/PdkOrder.php tests/Unit/App/Order/Model/PdkOrderContractIdTest.php
git commit -m "feat(order): propagate contractId from delivery options to shipment"
```

---

### Task 5: Replace local schema validation with capabilities API in `DeliveryOptionsService`

This is the core task. Replace the schema-based carrier filtering in `getValidCarrierOptions()` with the contextual capabilities API. This also resolves the TODO at line 289 ("support full package type class instead of string") since the entire schema call is removed.

**Files:**

- Modify: `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`
- Create: `tests/Unit/App/DeliveryOptions/Service/DeliveryOptionsServiceCapabilitiesTest.php`

- [ ] **Step 1: Read existing DeliveryOptionsService tests**

Read `tests/Unit/App/DeliveryOptions/Service/DeliveryOptionsServiceTest.php` (or find it via glob) to understand how the service is instantiated, how carrier settings are bootstrapped, how carts are constructed, and what assertions exist. This informs the exact test setup needed.

- [ ] **Step 2: Write failing tests for capabilities-based carrier filtering**

Cover behavioral scenarios 1-10 from the test matrix:

```php
<?php
// tests/Unit/App/DeliveryOptions/Service/DeliveryOptionsServiceCapabilitiesTest.php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\MockableCapabilitiesService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Helper to build a mock capabilities API response item.
 */
function capabilityResult(
    string $carrier,
    int $contractId,
    array $packageTypes,
    array $deliveryTypes = ['STANDARD_DELIVERY'],
    int $weightMin = 1,
    int $weightMax = 23000
): array {
    return [
        'carrier'             => $carrier,
        'contract'            => ['id' => $contractId, 'type' => 'main'],
        'package_types'       => $packageTypes,
        'options'             => (object) [],
        'physical_properties' => ['weight' => ['min' => $weightMin, 'max' => $weightMax]],
        'delivery_types'      => $deliveryTypes,
        'transaction_types'   => [],
        'collo'               => ['max' => 1],
    ];
}

// Scenario 2: package type filtering
it('filters carriers by package type availability from capabilities API', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)->store();

    TestBootstrapper::hasCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL     => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => true],
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => true],
    ]);

    // @TODO: Bind mock CarrierCapabilitiesRepository into DI container.
    // PostNL supports MAILBOX, DHL_FOR_YOU does not
    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [
            capabilityResult('POSTNL', 100, ['PACKAGE', 'MAILBOX']),
            capabilityResult('DHL_FOR_YOU', 200, ['PACKAGE']),
        ],
    ])));

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $cart = new PdkCart([
        'shippingMethod' => ['hasDeliveryOptions' => true, 'shippingAddress' => ['cc' => 'NL']],
        'lines'          => [['product' => ['settings' => ['packageType' => 'mailbox']]]],
    ]);

    $result = $service->createAllCarrierSettings($cart);
    expect(array_keys($result['carrierSettings']))->toHaveCount(1);
});

// Scenario 4: weight exceeds max
it('excludes carriers when cart weight exceeds capabilities weight max', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();
    TestBootstrapper::hasCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => true],
    ]);

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 1, 23000)],
    ])));

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $cart = new PdkCart([
        'shippingMethod' => ['hasDeliveryOptions' => true, 'shippingAddress' => ['cc' => 'NL']],
        'lines'          => [['quantity' => 1, 'product' => ['weight' => 25000, 'settings' => ['packageType' => 'package']]]],
    ]);

    expect($service->createAllCarrierSettings($cart)['carrierSettings'])->toBeEmpty();
});

// Scenario 5: weight below min
it('excludes carriers when cart weight is below capabilities weight min', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::GLS)->store();
    TestBootstrapper::hasCarrierSettings([
        RefCapabilitiesSharedCarrierV2::GLS => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => true],
    ]);

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [capabilityResult('GLS', 300, ['PACKAGE'], ['STANDARD_DELIVERY'], 100, 30000)],
    ])));

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $cart = new PdkCart([
        'shippingMethod' => ['hasDeliveryOptions' => true, 'shippingAddress' => ['cc' => 'NL']],
        'lines'          => [['quantity' => 1, 'product' => ['weight' => 50, 'settings' => ['packageType' => 'package']]]],
    ]);

    expect($service->createAllCarrierSettings($cart)['carrierSettings'])->toBeEmpty();
});

// Scenario 7: disabled carriers excluded
it('excludes carriers with delivery options disabled', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();
    TestBootstrapper::hasCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => false],
    ]);

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [capabilityResult('POSTNL', 100, ['PACKAGE'])],
    ])));

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $cart = new PdkCart([
        'shippingMethod' => ['hasDeliveryOptions' => true, 'shippingAddress' => ['cc' => 'NL']],
        'lines'          => [['product' => ['settings' => ['packageType' => 'package']]]],
    ]);

    expect($service->createAllCarrierSettings($cart)['carrierSettings'])->toBeEmpty();
});

// Scenario 10: contract ID in output
it('includes contractId from capabilities response in carrier settings', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();
    TestBootstrapper::hasCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL => [CarrierSettings::DELIVERY_OPTIONS_ENABLED => true],
    ]);

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [capabilityResult('POSTNL', 777, ['PACKAGE'])],
    ])));

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $cart = new PdkCart([
        'shippingMethod' => ['hasDeliveryOptions' => true, 'shippingAddress' => ['cc' => 'NL']],
        'lines'          => [['product' => ['settings' => ['packageType' => 'package']]]],
    ]);

    $result      = $service->createAllCarrierSettings($cart);
    $carrierKeys = array_keys($result['carrierSettings']);

    expect($carrierKeys)->not->toBeEmpty()
        ->and($result['carrierSettings'][$carrierKeys[0]])->toHaveKey('contractId', 777);
});
```

> **Note to implementing agent:** The `@TODO` markers indicate where the DI mock binding needs to be figured out. Read the existing `DeliveryOptionsServiceTest.php` test bootstrap to determine the correct approach. The assertions are correct — only the DI wiring may need adapting.

- [ ] **Step 3: Run tests to verify they fail**

Run: `docker compose run php composer test -- --filter="DeliveryOptionsServiceCapabilitiesTest"`
Expected: FAIL

- [ ] **Step 4: Replace SchemaRepository with CarrierCapabilitiesRepository in DeliveryOptionsService**

**4a. Replace the constructor dependency:**

Remove `use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;`, add `use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;`. Replace the constructor parameter, property, and assignment.

**4b. Rewrite `getValidCarrierOptions()`:**

Replace the method entirely. Key implementation details:

- Call `$this->capabilitiesRepository->getCapabilitiesForRecipient($cc)` once
- Index results by carrier name
- Filter by `deliveryOptionsEnabled`, package type (`in_array` on `getPackageTypes()`), and weight (`getPhysicalProperties()->getWeight()->getMin()/getMax()`)
- Attach `contractId` from `getContract()->getId()` to each matching carrier
- Include code comment explaining the broad-call-with-local-filtering design decision

**4c. Pass contract ID through in `createAllCarrierSettings()`:**

```php
$settings['carrierSettings'][$identifier] = array_merge(
    $this->createCarrierSettings($carrier, $cart, $packageType),
    ['contractId' => $carrier->contractId ?? null]
);
```

- [ ] **Step 5: Run new tests to verify they pass**

Run: `docker compose run php composer test -- --filter="DeliveryOptionsServiceCapabilitiesTest"`
Expected: PASS

- [ ] **Step 6: Run full test suite, fix regressions**

Run: `yarn run test`

Fix any tests that depended on SchemaRepository being injected into DeliveryOptionsService by updating DI bindings or replacing schema mocks with capabilities API mocks.

- [ ] **Step 7: Commit**

```bash
git add src/App/DeliveryOptions/Service/DeliveryOptionsService.php tests/
git commit -m "feat(checkout): determine carrier and package type availability from capabilities API

Replace local JSON schema validation in DeliveryOptionsService.getValidCarrierOptions()
with the contextual capabilities API. Package type and weight filtering now use runtime
data from POST /shipments/capabilities instead of static schema files.

The capabilities call uses only recipient country for cacheability — weight and
package type are filtered locally. See code comments for design rationale.

Contract ID from the capabilities response is included in carrier settings output.

Resolves TODO at DeliveryOptionsService.php:289 (package type class support)."
```

---

### Task 6: Update DI configuration if needed

- [ ] **Step 1: Find the DI configuration**

```bash
grep -r "DeliveryOptionsService" config/ --include="*.php" -l
```

If auto-wired, no changes needed. If explicitly bound, replace `SchemaRepository` with `CarrierCapabilitiesRepository`.

- [ ] **Step 2: Update and test**

Run: `yarn run test`
Expected: PASS

- [ ] **Step 3: Commit (if changes were needed)**

```bash
git add config/ src/
git commit -m "chore: update DeliveryOptionsService DI binding"
```

---

### Task 7: Verify cleanup and remaining SchemaRepository consumers

- [ ] **Step 1: Verify no SchemaRepository references remain in delivery options module**

```bash
grep -r "SchemaRepository" src/App/DeliveryOptions/ --include="*.php"
```

Expected: No results

- [ ] **Step 2: Document remaining SchemaRepository consumers**

```bash
grep -r "SchemaRepository" src/ --include="*.php" -l
```

Expected remaining consumers (left for follow-up plans):

- `src/App/Order/Calculator/General/InsuranceCalculator.php` — Plan 3
- `src/Validation/Repository/SchemaRepository.php` — the class itself

- [ ] **Step 3: Verify no stale imports in modified files**

- [ ] **Step 4: Run full test suite**

Run: `yarn run test`
Expected: PASS

- [ ] **Step 5: Commit cleanup**

```bash
git add -A
git commit -m "chore: remove stale SchemaRepository references from checkout module"
```

---

### Task 8: Multi-PHP verification and PR preparation

- [ ] **Step 1: Run on PHP 7.4**

```bash
PHP_VERSION=7.4 docker compose run php composer update --no-interaction --no-progress && yarn run test
```

- [ ] **Step 2: Run on PHP 8.1+**

```bash
PHP_VERSION=8.1 docker compose run php composer update --no-interaction --no-progress && yarn run test
```

- [ ] **Step 3: Verify test coverage**

| Code path                                                        | Covered in |
| ---------------------------------------------------------------- | ---------- |
| `CarrierCapabilitiesRepository.getCapabilitiesForRecipient()`    | Task 1     |
| `Carrier.contractId`                                             | Task 2     |
| `DeliveryOptions.contractId`                                     | Task 3     |
| `PdkOrder.createShipment()` contractId propagation               | Task 4     |
| `DeliveryOptionsService.getValidCarrierOptions()` scenarios 1-10 | Task 5     |

- [ ] **Step 4: Prepare PR**

Title: `feat(checkout): determine carrier and package type availability from API`

Body:

```
## Summary

- Carrier and package type filtering in checkout now uses the contextual
  capabilities API instead of local JSON schema files
- Weight validation uses physicalProperties from the API response
- Contract ID from the API is propagated through delivery options to shipments

## Behavioral changes

None for the consumer — the same carriers and package types appear in checkout
for the same cart contents. The data source changed from local schemas to the API.

New: the API-resolved contract ID is now included on exported shipments.

## Technical details

- `DeliveryOptionsService` no longer depends on `SchemaRepository` (breaking
  constructor change for platform implementations that override DI)
- `CarrierCapabilitiesRepository.getCapabilitiesForRecipient(cc)` calls
  `POST /shipments/capabilities` with `{ recipient: { cc } }` — one API call
  per checkout render, cached per country code. Weight and package type are
  filtered locally to keep the response cacheable across varying cart contents.
- `DeliveryOptions` model gains `contractId` attribute (int, nullable)
- `PdkOrder.createShipment()` propagates `contractId` to `Shipment`
- `SchemaRepository` is still used by `InsuranceCalculator` — removed in follow-up

## Resolved TODOs

- `CarrierCapabilitiesRepository.php:59` — placeholder implementation
- `DeliveryOptionsService.php:289` — package type class support

## Test plan

- [ ] Checkout with standard package, NL address — all enabled carriers shown
- [ ] Checkout with mailbox items — only MAILBOX-capable carriers shown
- [ ] Checkout with digital stamp items — only DIGITAL_STAMP-capable carriers shown
- [ ] Cart weight > carrier max — carrier hidden
- [ ] Cart weight < carrier min — carrier hidden
- [ ] International address — country-appropriate carriers and package types
- [ ] Disabled carrier — never shown
- [ ] No matching carriers — fallback to default package type
- [ ] Mixed cart — largest package type wins
- [ ] Export shipment — contract ID present in API request
```

---

## Summary of changes per file

| File                                                                                | Change                                                                                                                        |
| ----------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `src/Carrier/Repository/CarrierCapabilitiesRepository.php`                          | Implement `getCapabilitiesForRecipient()`, resolve placeholder TODO                                                           |
| `src/Carrier/Model/Carrier.php`                                                     | Add transient `$contractId` property                                                                                          |
| `src/Shipment/Model/DeliveryOptions.php`                                            | Add `contractId` attribute + cast                                                                                             |
| `src/App/Order/Model/PdkOrder.php`                                                  | Propagate `contractId` in `createShipment()`                                                                                  |
| `src/App/DeliveryOptions/Service/DeliveryOptionsService.php`                        | Replace `SchemaRepository` with `CarrierCapabilitiesRepository`, rewrite `getValidCarrierOptions()`, resolve TODO at line 289 |
| DI config (if explicit)                                                             | Update service binding                                                                                                        |
| `tests/Unit/Carrier/Repository/CarrierCapabilitiesRepositoryTest.php`               | New — capabilities API + caching tests                                                                                        |
| `tests/Unit/Carrier/Model/CarrierContractIdTest.php`                                | New — transient contractId tests                                                                                              |
| `tests/Unit/Shipment/Model/DeliveryOptionsContractIdTest.php`                       | New — contractId attribute tests                                                                                              |
| `tests/Unit/App/Order/Model/PdkOrderContractIdTest.php`                             | New — contractId propagation tests                                                                                            |
| `tests/Unit/App/DeliveryOptions/Service/DeliveryOptionsServiceCapabilitiesTest.php` | New — all behavioral scenario tests                                                                                           |
