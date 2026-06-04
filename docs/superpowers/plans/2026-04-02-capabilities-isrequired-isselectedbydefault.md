# Capabilities isRequired/isSelectedByDefault Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrate `isRequired` and `isSelectedByDefault` from carrier contract definitions into shipment option resolution and frontend settings, so required options are always enforced and capability defaults apply as the lowest-priority fallback across all resolution levels.

**Architecture:** A utility method on the `Carrier` model provides metadata lookup. A new `CapabilitiesDefaultHelper` sits at the end of the TriState resolution chain, providing `isSelectedByDefault` as the lowest-priority fallback — it applies unless explicitly overridden at ANY level (shipment options, product settings, or carrier settings). Post-resolution enforcement in `PdkOrderOptionsService` forces `isRequired` options to ENABLED regardless of what any source says. The `CarrierSettingsItemView` passes metadata to the frontend so required toggles are read-only.

**Tech Stack:** PHP 7.4+, Pest v1, SDK generated models (`RefCapabilitiesContractDefinitionsResponseOptionsOptionV2`)

---

## Behavior Rules

| Property                   | Behavior                                                                                                                                                                                 | Priority                                                 |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| `isRequired=true`          | Option is ALWAYS ENABLED. Cannot be turned off at any level. Post-resolution enforcement overrides all sources.                                                                          | Highest — overrides everything                           |
| `isSelectedByDefault=true` | Option defaults to ENABLED. Acts as the lowest-priority layer in the resolution chain. Any explicit value at shipment options, product settings, or carrier settings level overrides it. | Lowest — only applies when all other sources are INHERIT |

**Resolution chain (highest to lowest priority):**

1. ShipmentOptionsHelper — existing order/shipment values
2. ProductSettingsHelper — product-level settings from order lines
3. CarrierSettingsHelper — user-configured carrier export defaults
4. **CapabilitiesDefaultHelper** (NEW) — `isSelectedByDefault` from carrier contract definitions
5. TriState fallback → DISABLED

**Post-resolution override:** After resolution, `isRequired=true` forces ENABLED regardless of the resolved value.

---

## File Structure

| File                                                              | Action           | Responsibility                                                                                 |
| ----------------------------------------------------------------- | ---------------- | ---------------------------------------------------------------------------------------------- |
| `src/Carrier/Model/Carrier.php`                                   | Modify           | Add `getOptionMetadata()` to look up `isRequired`/`isSelectedByDefault` for a capabilities key |
| `src/App/Options/Helper/CapabilitiesDefaultHelper.php`            | Create           | New helper at end of resolution chain, returns ENABLED when `isSelectedByDefault=true`         |
| `src/App/Order/Service/PdkOrderOptionsService.php`                | Modify           | Add `CapabilitiesDefaultHelper` to helper chain + post-resolution enforcement of `isRequired`  |
| `src/Frontend/View/CarrierSettingsItemView.php`                   | Modify           | Pass `isRequired` metadata to form elements so frontend can disable toggles                    |
| `tests/Unit/Carrier/Model/CarrierTest.php`                        | Modify           | Tests for `getOptionMetadata()`                                                                |
| `tests/Unit/App/Options/Helper/CapabilitiesDefaultHelperTest.php` | Create           | Tests for the new helper                                                                       |
| `tests/Unit/App/Order/Service/PdkOrderOptionsServiceTest.php`     | Create           | Tests for `isRequired` enforcement and `isSelectedByDefault` fallback across all levels        |
| `tests/Unit/Frontend/View/CarrierSettingsItemViewTest.php`        | Create or Modify | Tests for metadata on form elements                                                            |

---

### Task 1: Factory support for option metadata

All subsequent tasks need carrier fixtures with `isRequired=true` or `isSelectedByDefault=true` on specific options. Build these factory methods first.

**Files:**

- Modify: The carrier factory file (find via `grep -r "class CarrierFactory" tests/`)

- [ ] **Step 1: Find the carrier factory**

Run: `grep -r "class CarrierFactory" tests/`

- [ ] **Step 2: Add factory methods**

The factory should modify the carrier's SDK-backed options object to set `isRequired=true` or `isSelectedByDefault=true` on a specific option identified by its camelCase capabilities key.

```php
/**
 * Set isRequired=true on a specific carrier option.
 *
 * @param  string $capabilitiesKey camelCase key, e.g. 'requiresSignature'
 *
 * @return self
 */
public function withOptionRequired(string $capabilitiesKey): self
{
    return $this->withOptionMetadata($capabilitiesKey, 'setIsRequired', true);
}

/**
 * Set isSelectedByDefault=true on a specific carrier option.
 *
 * @param  string $capabilitiesKey camelCase key, e.g. 'requiresSignature'
 *
 * @return self
 */
public function withOptionSelectedByDefault(string $capabilitiesKey): self
{
    return $this->withOptionMetadata($capabilitiesKey, 'setIsSelectedByDefault', true);
}

/**
 * @param  string $capabilitiesKey
 * @param  string $setter
 * @param  bool   $value
 *
 * @return self
 */
private function withOptionMetadata(string $capabilitiesKey, string $setter, bool $value): self
{
    // Adapt this to the actual factory mutation pattern used in this project.
    // The key insight: after the carrier is built with withAllCapabilities(),
    // we need to reach into carrier->options->getRequiresSignature()->setIsRequired(true).
    // Check how existing with* methods modify the underlying SDK model data.
    return $this->with(function (Carrier $carrier) use ($capabilitiesKey, $setter, $value) {
        $getter = 'get' . ucfirst($capabilitiesKey);
        $option = $carrier->options->$getter();

        if ($option && method_exists($option, $setter)) {
            $option->$setter($value);
        }
    });
}
```

Adapt to the actual factory pattern — check existing `with*` methods on the carrier factory for the correct approach (e.g., whether they modify attributes arrays, use callbacks, or call setters on the model). The `with()` callback shown above is a placeholder for whatever mutation mechanism the factory uses.

- [ ] **Step 3: Write a quick smoke test**

```php
it('factory can set option isRequired', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->make();

    $option = $carrier->options->getRequiresSignature();

    expect($option->getIsRequired())->toBeTrue();
});

it('factory can set option isSelectedByDefault', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    $option = $carrier->options->getRequiresSignature();

    expect($option->getIsSelectedByDefault())->toBeTrue();
});
```

- [ ] **Step 4: Run smoke tests**

Run: `docker compose run php composer test -- --filter="factory can set option"`
Expected: PASS

- [ ] **Step 5: Commit**

```
test(factory): add withOptionRequired and withOptionSelectedByDefault to CarrierFactory
```

---

### Task 2: Add `getOptionMetadata()` to Carrier Model

Foundation utility to look up `isRequired`/`isSelectedByDefault` for a given capabilities key on a carrier.

**Files:**

- Modify: `src/Carrier/Model/Carrier.php`
- Test: `tests/Unit/Carrier/Model/CarrierTest.php`

- [ ] **Step 1: Write the failing test**

In `tests/Unit/Carrier/Model/CarrierTest.php`, add:

```php
it('returns option metadata for a capabilities key', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    $metadata = $carrier->getOptionMetadata('requiresSignature');

    expect($metadata)->toBeArray()
        ->and($metadata)->toHaveKeys(['isRequired', 'isSelectedByDefault'])
        ->and($metadata['isRequired'])->toBeBool()
        ->and($metadata['isSelectedByDefault'])->toBeBool();
});

it('returns null metadata for unknown capabilities key', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    $metadata = $carrier->getOptionMetadata('nonExistentOption');

    expect($metadata)->toBeNull();
});

it('returns null metadata when carrier has no options', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->make();

    $metadata = $carrier->getOptionMetadata('requiresSignature');

    expect($metadata)->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="returns option metadata"`
Expected: FAIL — `getOptionMetadata` method does not exist

- [ ] **Step 3: Write the implementation**

In `src/Carrier/Model/Carrier.php`, add this method:

```php
/**
 * Get isRequired/isSelectedByDefault metadata for a shipment option by its capabilities key.
 *
 * @param  string $capabilitiesKey camelCase key, e.g. 'requiresSignature'
 *
 * @return null|array{isRequired: bool, isSelectedByDefault: bool}
 */
public function getOptionMetadata(string $capabilitiesKey): ?array
{
    if (! $this->options) {
        return null;
    }

    // capabilitiesKey is camelCase (e.g. 'requiresSignature')
    // The getter on the options container is 'get' + ucfirst (e.g. 'getRequiresSignature')
    $getter = 'get' . ucfirst($capabilitiesKey);

    if (! method_exists($this->options, $getter)) {
        return null;
    }

    $option = $this->options->$getter();

    if (! $option || ! method_exists($option, 'getIsRequired')) {
        return null;
    }

    return [
        'isRequired'          => (bool) $option->getIsRequired(),
        'isSelectedByDefault' => (bool) $option->getIsSelectedByDefault(),
    ];
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker compose run php composer test -- --filter="returns option metadata|returns null metadata"`
Expected: PASS

- [ ] **Step 5: Commit**

```
feat(carrier): add getOptionMetadata() for capabilities isRequired/isSelectedByDefault lookup
```

---

### Task 3: Create `CapabilitiesDefaultHelper`

New helper that sits at the end of the resolution chain. Returns ENABLED when `isSelectedByDefault=true` for the carrier's option, INHERIT otherwise. This makes it the lowest-priority default — any explicit value from shipment options, product settings, or carrier settings overrides it.

**Files:**

- Create: `src/App/Options/Helper/CapabilitiesDefaultHelper.php`
- Create: `tests/Unit/App/Options/Helper/CapabilitiesDefaultHelperTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/App/Options/Helper/CapabilitiesDefaultHelperTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Helper\CapabilitiesDefaultHelper;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('returns ENABLED when isSelectedByDefault is true', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    expect($helper->get(new SignatureDefinition()))->toEqual(TriStateService::ENABLED);
});

it('returns INHERIT when isSelectedByDefault is false', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    expect($helper->get(new AgeCheckDefinition()))->toEqual(TriStateService::INHERIT);
});

it('returns INHERIT when definition has no capabilities key', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    // Use a mock definition that returns null for getCapabilitiesOptionsKey
    $definition = Mockery::mock(\MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface::class);
    $definition->shouldReceive('getCapabilitiesOptionsKey')->andReturn(null);

    expect($helper->get($definition))->toEqual(TriStateService::INHERIT);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="CapabilitiesDefaultHelperTest"`
Expected: FAIL — class `CapabilitiesDefaultHelper` does not exist

- [ ] **Step 3: Write the implementation**

Create `src/App/Options/Helper/CapabilitiesDefaultHelper.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Provides isSelectedByDefault from carrier capabilities as the lowest-priority default.
 *
 * Sits at the end of the resolution chain so it only takes effect when all other
 * sources (shipment options, product settings, carrier settings) are INHERIT.
 */
final class CapabilitiesDefaultHelper implements OptionDefinitionHelperInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private $order;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return int
     */
    public function get(OrderOptionDefinitionInterface $definition)
    {
        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if (! $capabilitiesKey) {
            return TriStateService::INHERIT;
        }

        $carrier = $this->order->deliveryOptions->carrier;

        if (! $carrier) {
            return TriStateService::INHERIT;
        }

        $metadata = $carrier->getOptionMetadata($capabilitiesKey);

        if ($metadata && $metadata['isSelectedByDefault']) {
            return TriStateService::ENABLED;
        }

        return TriStateService::INHERIT;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker compose run php composer test -- --filter="CapabilitiesDefaultHelperTest"`
Expected: PASS

- [ ] **Step 5: Commit**

```
feat(options): add CapabilitiesDefaultHelper for isSelectedByDefault fallback
```

---

### Task 4: Integrate into `PdkOrderOptionsService`

Add `CapabilitiesDefaultHelper` at the end of the helper chain and add post-resolution enforcement for `isRequired`. This is the task that wires everything together.

**Files:**

- Modify: `src/App/Order/Service/PdkOrderOptionsService.php`
- Create: `tests/Unit/App/Order/Service/PdkOrderOptionsServiceTest.php`

- [ ] **Step 1: Write failing tests for isRequired enforcement**

Create `tests/Unit/App/Order/Service/PdkOrderOptionsServiceTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('forces ENABLED for isRequired even when all sources say DISABLED', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // Carrier settings: DISABLED
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => false])
        ->store();

    // Shipment options: DISABLED
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

it('does not force ENABLED when isRequired is false', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => false])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});
```

- [ ] **Step 2: Write failing tests for isSelectedByDefault across all levels**

Add to the same test file:

```php
it('isSelectedByDefault applies when all sources are INHERIT', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // No carrier settings stored (defaults to INHERIT)
    // Shipment options: INHERIT
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

it('carrier settings override isSelectedByDefault', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // Carrier settings explicitly DISABLED — overrides isSelectedByDefault
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => false])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});

it('shipment options override isSelectedByDefault', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // Shipment options explicitly DISABLED — overrides isSelectedByDefault
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});

it('isSelectedByDefault applies to inherited delivery options (EXCLUDE_SHIPMENT_OPTIONS)', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // No carrier settings stored — isSelectedByDefault should apply
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions(
        $order,
        PdkOrderOptionsServiceInterface::EXCLUDE_SHIPMENT_OPTIONS
    );

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `docker compose run php composer test -- --filter="PdkOrderOptionsServiceTest"`
Expected: FAIL — isRequired not enforced, isSelectedByDefault not applied

- [ ] **Step 4: Write the implementation**

Modify `src/App/Order/Service/PdkOrderOptionsService.php` — update `calculateShipmentOptions()`:

```php
public function calculateShipmentOptions(PdkOrder $order, int $flags = 0): PdkOrder
{
    $helpers = Arr::flatten([
        $flags & self::EXCLUDE_SHIPMENT_OPTIONS ? [] : [new ShipmentOptionsDefinitionHelper($order)],
        $flags & self::EXCLUDE_PRODUCT_SETTINGS ? [] : [new ProductSettingsDefinitionHelper($order)],
        $flags & self::EXCLUDE_CARRIER_SETTINGS ? [] : [new CarrierSettingsDefinitionHelper($order)],
        // Capabilities default is always included — lowest priority fallback
        new CapabilitiesDefaultHelper($order),
    ]);

    /** @var OrderOptionDefinitionInterface[] $definitions */
    $definitions = Pdk::get('orderOptionDefinitions');

    $carrier = $order->deliveryOptions->carrier;

    foreach ($definitions as $definition) {
        $values = array_map(static function (OptionDefinitionHelperInterface $helper) use ($definition) {
            return $helper->get($definition);
        }, $helpers);

        $value = $this->triStateService->resolve(...$values);

        // Enforce isRequired: if the carrier capability requires this option, force ENABLED
        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if ($capabilitiesKey && $carrier) {
            $metadata = $carrier->getOptionMetadata($capabilitiesKey);

            if ($metadata && $metadata['isRequired']) {
                $value = TriStateService::ENABLED;
            }
        }

        $order->deliveryOptions->shipmentOptions->setAttribute($definition->getShipmentOptionsKey(), $value);
    }

    return $order;
}
```

Add the import at the top of the file:

```php
use MyParcelNL\Pdk\App\Options\Helper\CapabilitiesDefaultHelper;
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `docker compose run php composer test -- --filter="PdkOrderOptionsServiceTest"`
Expected: PASS — all 6 tests

- [ ] **Step 6: Run full test suite to check for regressions**

Run: `docker compose run php composer test`
Expected: All passing. If any tests break because they didn't expect the new behavior, update those tests — the new behavior is correct.

- [ ] **Step 7: Commit**

```
feat(options): integrate isRequired enforcement and isSelectedByDefault fallback into resolution chain
```

---

### Task 5: Pass `isRequired` metadata to frontend settings form elements

The `CarrierSettingsItemView` builds toggle elements for shipment options. For options where `isRequired=true`, the toggle should be read-only (always on).

**Files:**

- Modify: `src/Frontend/View/CarrierSettingsItemView.php`
- Test: `tests/Unit/Frontend/View/CarrierSettingsItemViewTest.php` (create if not exists)

- [ ] **Step 1: Write the failing test**

Create or modify `tests/Unit/Frontend/View/CarrierSettingsItemViewTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Frontend\View\CarrierSettingsItemView;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('marks form elements as readOnly when carrier option isRequired is true', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    $view     = new CarrierSettingsItemView($carrier);
    $elements = $view->toArray();

    // Find the signature toggle element and verify it has a readOnly builder.
    // The exact assertion depends on how form element serialization works —
    // look for '$builders' containing a readOnly operation on the signature element.
    // Adapt the element name to match what CarrierSettingsItemView actually uses
    // (e.g., CarrierSettings::ALLOW_SIGNATURE or CarrierSettings::EXPORT_SIGNATURE).
    $flatElements = array_column($elements, null, 'name');

    $signatureElement = $flatElements['allowSignature'] ?? $flatElements['exportSignature'] ?? null;

    expect($signatureElement)->not->toBeNull()
        ->and($signatureElement)->toHaveKey('$builders');
});

it('does not mark form elements as readOnly when isRequired is false', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    $view     = new CarrierSettingsItemView($carrier);
    $elements = $view->toArray();

    $flatElements = array_column($elements, null, 'name');

    $signatureElement = $flatElements['allowSignature'] ?? $flatElements['exportSignature'] ?? null;

    // Element should exist but NOT have a readOnly builder (or no $builders key at all)
    expect($signatureElement)->not->toBeNull();

    if (isset($signatureElement['$builders'])) {
        // If builders exist (from other conditions), none should be a readOnly operation
        $hasReadOnly = array_filter($signatureElement['$builders'], function ($builder) {
            return ($builder['type'] ?? '') === 'readOnly';
        });
        expect($hasReadOnly)->toBeEmpty();
    }
});
```

Note: The exact element names and builder serialization format must match the real implementation. Inspect the output of `CarrierSettingsItemView::toArray()` in a debugger or print statement to determine the exact shape, then adjust assertions accordingly.

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose run php composer test -- --filter="marks form elements as readOnly"`
Expected: FAIL — no readOnly builder on the element

- [ ] **Step 3: Write the implementation**

In `src/Frontend/View/CarrierSettingsItemView.php`, modify `getShipmentOptionsSettings()` to apply readOnly when `isRequired=true`:

```php
private function getShipmentOptionsSettings(): array
{
    $settings = [];

    if ($this->carrierSchema->canHaveSignature()) {
        $elements = $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_SIGNATURE,
            CarrierSettings::PRICE_SIGNATURE
        );
        $this->applyRequiredMetadata($elements[0], 'requiresSignature');
        $settings = array_merge($settings, $elements);
    }

    if ($this->carrierSchema->canHaveOnlyRecipient()) {
        $elements = $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_ONLY_RECIPIENT,
            CarrierSettings::PRICE_ONLY_RECIPIENT
        );
        $this->applyRequiredMetadata($elements[0], 'recipientOnlyDelivery');
        $settings = array_merge($settings, $elements);
    }

    if ($this->carrierSchema->canHavePriorityDelivery()) {
        $elements = $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_PRIORITY_DELIVERY,
            CarrierSettings::PRICE_PRIORITY_DELIVERY
        );
        $this->applyRequiredMetadata($elements[0], 'priorityDelivery');
        $settings = array_merge($settings, $elements);
    }

    // Apply the same pattern to ALL other option elements in this method.
    // Each option needs: $this->applyRequiredMetadata($elements[0], '<capabilitiesKey>');
    // Use the capabilitiesKey from the corresponding OrderOptionDefinition.

    return $settings;
}
```

Add this helper method to `CarrierSettingsItemView`:

```php
/**
 * If the carrier capability has isRequired=true, make the form element read-only.
 *
 * @param  \MyParcelNL\Pdk\Frontend\Form\InteractiveElement $element
 * @param  string                                            $capabilitiesKey
 *
 * @return void
 */
private function applyRequiredMetadata(InteractiveElement $element, string $capabilitiesKey): void
{
    $metadata = $this->carrier->getOptionMetadata($capabilitiesKey);

    if ($metadata && $metadata['isRequired']) {
        $element->builder(function (FormOperationBuilder $builder) {
            $builder->readOnlyWhen();
        });
    }
}
```

Ensure `$this->carrier` is available on the view. Check if the constructor already stores it — if only `$this->carrierSchema` is stored, also store a reference to the carrier:

```php
// In constructor, add:
$this->carrier = $carrier;
```

Add the required imports:

```php
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker compose run php composer test -- --filter="CarrierSettingsItemViewTest"`
Expected: PASS

- [ ] **Step 5: Commit**

```
feat(frontend): mark required carrier options as readOnly in settings form
```

---

### Task 6: Integration tests for context flows

Verify that the calculation changes automatically propagate to order context (inherited delivery options) and checkout context. These flows call `calculateShipmentOptions()` internally, so they should benefit from Tasks 3-4 without additional code changes.

**Files:**

- Create or modify: Context-related test files

- [ ] **Step 1: Trace checkout context flow**

Read `src/Shipment/Service/DeliveryOptionsService.php` and verify `createAllCarrierSettings()` uses the same `calculateShipmentOptions()` path. If it does NOT, it needs to be updated to go through the same resolution flow so `isRequired`/`isSelectedByDefault` apply there too.

- [ ] **Step 2: Write integration test for inherited delivery options with isRequired**

```php
it('inherited delivery options enforce isRequired from carrier capabilities', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // Carrier settings have signature DISABLED
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => false])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $context   = new OrderDataContext($order->toArray());
    $inherited = $context->inheritedDeliveryOptions;

    $postnlOptions = $inherited['POSTNL'] ?? null;

    expect($postnlOptions)->not->toBeNull()
        ->and($postnlOptions['requiresSignature'] ?? null)->toBe(TriStateService::ENABLED);
});
```

- [ ] **Step 3: Write integration test for inherited delivery options with isSelectedByDefault**

```php
it('inherited delivery options apply isSelectedByDefault when no carrier setting exists', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // No carrier settings stored

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $context   = new OrderDataContext($order->toArray());
    $inherited = $context->inheritedDeliveryOptions;

    $postnlOptions = $inherited['POSTNL'] ?? null;

    expect($postnlOptions)->not->toBeNull()
        ->and($postnlOptions['requiresSignature'] ?? null)->toBe(TriStateService::ENABLED);
});

it('inherited delivery options respect carrier setting over isSelectedByDefault', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->make();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrier))
        ->store();

    // Explicitly DISABLE signature in carrier settings — overrides isSelectedByDefault
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => false])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrier))
        ->make();

    $context   = new OrderDataContext($order->toArray());
    $inherited = $context->inheritedDeliveryOptions;

    $postnlOptions = $inherited['POSTNL'] ?? null;

    expect($postnlOptions)->not->toBeNull()
        ->and($postnlOptions['requiresSignature'] ?? null)->toBe(TriStateService::DISABLED);
});
```

- [ ] **Step 4: Run integration tests**

Run: `docker compose run php composer test -- --filter="inherited delivery options"`
Expected: PASS (if Tasks 3-4 are implemented correctly, these should pass without extra code)

- [ ] **Step 5: Run full test suite on PHP 7.4 and 8.5**

Run:

```bash
docker compose run php composer test
PHP_VERSION=8.5 docker compose run php composer update --no-interaction --no-progress && PHP_VERSION=8.5 docker compose run php composer test
```

Expected: All passing on both PHP versions.

- [ ] **Step 6: Commit**

```
test: add integration tests for isRequired/isSelectedByDefault in context flows
```
