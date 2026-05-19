# Schema cleanup part 1: drop dead OrderOptionDefinition classes — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Delete six `OrderOptionDefinition` subclasses that are unregistered in `orderOptionDefinitions` config and have zero production references (`CountryOfOriginDefinition`, `CustomsCodeDefinition`, `DisableDeliveryOptionsDefinition`, `FitInDigitalStampDefinition`, `FitInMailboxDefinition`, `PackageTypeDefinition`). Update the four test files that reference them, replacing Definition class references with the production-equivalent paths (direct property keys on `ProductSettings`) where coverage is still meaningful.

**Architecture:** The 6 classes are inert: they extend `AbstractOrderOptionDefinition` but are never registered in `config/pdk-business-logic.php`, never instantiated outside tests, and never read in production code. The concepts they represent (country of origin, customs code, fit-in-mailbox, package type, etc.) ARE alive — but via hand-declared `ProductSettings::*` properties consumed directly by `CartCalculationService` and similar, not via the Definition class. Tests that exercised these Definitions either lose meaning (and are pruned) or get rewritten to drive the same coverage through direct property keys.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep. The `OrderOptionDefinitionInterfaceTest` uses snapshot tests via Spatie — snapshots will need regeneration.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Schema A-1..A-6 and B-1. Supporting analysis in `docs/superpowers/findings/2026-05-11-six-unused-definitions-overview.md`.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work — plan docs and implementation commits — lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`.

---

## File structure

| File                                                                                              | Action     | Responsibility                                                                                                                                                                                                                                                                                                                                                                                                       |
| ------------------------------------------------------------------------------------------------- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `src/App/Options/Definition/CountryOfOriginDefinition.php`                                        | Delete     | Inert Definition; not registered, only used in tests.                                                                                                                                                                                                                                                                                                                                                                |
| `src/App/Options/Definition/CustomsCodeDefinition.php`                                            | Delete     | Same.                                                                                                                                                                                                                                                                                                                                                                                                                |
| `src/App/Options/Definition/DisableDeliveryOptionsDefinition.php`                                 | Delete     | Same. The `disableDeliveryOptions` concept lives on `ProductSettings`.                                                                                                                                                                                                                                                                                                                                               |
| `src/App/Options/Definition/FitInDigitalStampDefinition.php`                                      | Delete     | Same. Digital-stamp logic lives in `WeightCalculator` / `CapabilitiesValidationService`.                                                                                                                                                                                                                                                                                                                             |
| `src/App/Options/Definition/FitInMailboxDefinition.php`                                           | Delete     | Same. `fitInMailbox` is on `ProductSettings`, consumed by `CartCalculationService`.                                                                                                                                                                                                                                                                                                                                  |
| `src/App/Options/Definition/PackageTypeDefinition.php`                                            | Delete     | Same. `packageType` is on `ProductSettings`.                                                                                                                                                                                                                                                                                                                                                                         |
| `tests/Datasets/shipmentOptions.php`                                                              | Modify     | Drop 6 imports, drop `getProductOptions()` and its `'product options'` dataset (no external consumers — verified).                                                                                                                                                                                                                                                                                                   |
| `tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php`                           | Modify     | Drop 6 imports + 6 dataset rows from each `with([...])`. Remaining 5 rows still exercise the helper.                                                                                                                                                                                                                                                                                                                 |
| `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php`                          | Modify     | Drop 6 imports + 6 entries from `$definitions`. Also remove already-dead imports (`PropositionCarrierFeatures`, `CarrierCapabilities` — both deleted on the v4 branch). Regenerate snapshot.                                                                                                                                                                                                                         |
| `tests/Unit/App/Order/Model/PdkProductTest.php`                                                   | Modify     | Refactor the `'calculates other options for child products'` test to pass property-key strings instead of Definition class refs. Refactor `createNestedProducts` to take `string $key` instead of `OrderOptionDefinitionInterface $definition`. Keep the existing `'merges parent settings correctly'` test working by also updating its single `SignatureDefinition` dataset entry to the corresponding string key. |
| `tests/__snapshots__/...OrderOptionDefinitionInterfaceTest__it_snapshots_all_definitions__1.json` | Regenerate | Existing snapshot covers 15 definitions; after cleanup it covers 9.                                                                                                                                                                                                                                                                                                                                                  |

No `src/` files outside the 6 Definition classes themselves reference them (verified — see Task 1 Step 4).

---

## Task 1: Baseline verification

**Files:**

- No edits.

- [ ] **Step 1: Verify branch**

Run:

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 2: Baseline test run**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log
echo "exit: $?"
```

Expected: tests pass. Capture for diff later.

- [ ] **Step 3: Baseline PHPStan**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log
echo "exit: $?"
```

Expected: zero errors.

- [ ] **Step 4: Confirm assumption — the 6 Definitions have only the expected reference set**

Run:

```bash
for c in CountryOfOriginDefinition CustomsCodeDefinition DisableDeliveryOptionsDefinition FitInDigitalStampDefinition FitInMailboxDefinition PackageTypeDefinition; do
  echo "--- $c ---"
  rg -l "\b$c\b" src/ tests/ 2>/dev/null
done
```

Expected: each class returns its own definition file + exactly these test files (in some combination):

- `tests/Datasets/shipmentOptions.php`
- `tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php`
- `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php`
- `tests/Unit/App/Order/Model/PdkProductTest.php` (only for `CountryOfOriginDefinition`, `CustomsCodeDefinition`, `PackageTypeDefinition`)

If any other file appears, **stop** — the baseline has drifted and the plan needs review.

- [ ] **Step 5: Confirm plugin side has no references**

Run:

```bash
for c in CountryOfOriginDefinition CustomsCodeDefinition DisableDeliveryOptionsDefinition FitInDigitalStampDefinition FitInMailboxDefinition PackageTypeDefinition; do
  hits=$(rg -l "\b$c\b" ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null | wc -l | tr -d ' ')
  echo "$c → $hits plugin file refs"
done
```

Expected: all `0`. If any non-zero, **stop** — surface to the user before deleting.

- [ ] **Step 6: No commit.** Baseline only.

---

## Task 2: Prune `tests/Datasets/shipmentOptions.php`

**Files:**

- Modify: `tests/Datasets/shipmentOptions.php`

- [ ] **Step 1: Remove the 6 dead imports**

In `tests/Datasets/shipmentOptions.php`, delete these `use` lines:

```php
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DisableDeliveryOptionsDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInDigitalStampDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInMailboxDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
```

- [ ] **Step 2: Remove `getProductOptions()` function**

Delete the entire function block (currently lines 40-50):

```php
function getProductOptions(): array
{
    return array_merge(getAllShipmentOptions(), [
        'country of origin'        => new CountryOfOriginDefinition(),
        'customs code'             => new CustomsCodeDefinition(),
        'disable delivery options' => new DisableDeliveryOptionsDefinition(),
        'fit in digital stamp'     => new FitInDigitalStampDefinition(),
        'fit in mailbox'           => new FitInMailboxDefinition(),
        'package type'             => new PackageTypeDefinition(),
    ]);
}
```

- [ ] **Step 3: Remove the `'product options'` dataset registration**

Delete this line near the end of the file:

```php
dataset('product options', function () { return getProductOptions(); });
```

- [ ] **Step 4: Verify nothing in tests/ still references `getProductOptions` or `'product options'`**

Run:

```bash
rg 'getProductOptions|"product options"|'"'"'product options'"'"'' tests/
```

Expected: no output. (We verified there are no external consumers in Task 1.)

- [ ] **Step 5: Run the tests via Docker (intermediate check)**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tail -50
echo "exit: $?"
```

Expected: tests pass. (The 6 Definition classes still exist; we only removed the helper that referenced them.)

- [ ] **Step 6: No commit yet.** Continue.

---

## Task 3: Prune `tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php`

**Files:**

- Modify: `tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php`

- [ ] **Step 1: Remove 6 dead imports**

Delete these lines:

```php
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DisableDeliveryOptionsDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInDigitalStampDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInMailboxDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
```

- [ ] **Step 2: Remove 6 rows from the first test's dataset (`'gets value from product settings'`)**

Find this block (currently lines 38-51):

```php
])->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::INHERIT],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::INHERIT],
    'large format'   => [LargeFormatDefinition::class, TriStateService::INHERIT],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::INHERIT],
    'signature'      => [SignatureDefinition::class, TriStateService::INHERIT],

    'country of origin'        => [CountryOfOriginDefinition::class, TriStateService::INHERIT],
    'customs code'             => [CustomsCodeDefinition::class, TriStateService::INHERIT],
    'disable delivery options' => [DisableDeliveryOptionsDefinition::class, TriStateService::INHERIT],
    'fit in digital stamp'     => [FitInDigitalStampDefinition::class, TriStateService::INHERIT],
    'fit in mailbox'           => [FitInMailboxDefinition::class, TriStateService::INHERIT],
    'package type'             => [PackageTypeDefinition::class, TriStateService::INHERIT],
]);
```

Replace with:

```php
])->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::INHERIT],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::INHERIT],
    'large format'   => [LargeFormatDefinition::class, TriStateService::INHERIT],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::INHERIT],
    'signature'      => [SignatureDefinition::class, TriStateService::INHERIT],
]);
```

- [ ] **Step 3: Remove 5 rows from the second test's dataset (`'gets value from product settings with all options enabled'`)**

Find this block (currently lines 63-75):

```php
])->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::ENABLED],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::ENABLED],
    'large format'   => [LargeFormatDefinition::class, TriStateService::ENABLED],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::ENABLED],
    'signature'      => [SignatureDefinition::class, TriStateService::ENABLED],

    'country of origin'        => [CountryOfOriginDefinition::class, 'NL'],
    'customs code'             => [CustomsCodeDefinition::class, '123456'],
    'disable delivery options' => [DisableDeliveryOptionsDefinition::class, true],
    'fit in mailbox'           => [FitInMailboxDefinition::class, 0],
    'package type'             => [PackageTypeDefinition::class, DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
]);
```

Replace with:

```php
])->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::ENABLED],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::ENABLED],
    'large format'   => [LargeFormatDefinition::class, TriStateService::ENABLED],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::ENABLED],
    'signature'      => [SignatureDefinition::class, TriStateService::ENABLED],
]);
```

- [ ] **Step 4: Check whether `DeliveryOptions` import is still needed**

The `DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME` reference goes away with the `package type` row in Step 3. Run:

```bash
rg 'DeliveryOptions' tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php
```

If only the `use` line remains, delete it.

- [ ] **Step 5: Run the tests via Docker (intermediate check)**

Run:

```bash
docker compose run --rm php composer test -- --filter=ProductSettingsDefinitionHelper 2>&1 | tail -30
echo "exit: $?"
```

Expected: tests pass with reduced row counts (5 + 5 = 10 datasets, was 11 + 10 = 21).

- [ ] **Step 6: No commit yet.** Continue.

---

## Task 4: Prune `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php`

**Files:**

- Modify: `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php`
- Regenerate: `tests/__snapshots__/.../OrderOptionDefinitionInterfaceTest__it_snapshots_all_definitions__1.json` (path under tests/Unit; resolved by Pest)

- [ ] **Step 1: Remove 6 dead Definition imports**

Delete these lines:

```php
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DisableDeliveryOptionsDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInDigitalStampDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInMailboxDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
```

- [ ] **Step 2: Remove already-dead imports (cleanup hygiene)**

These two imports point to classes that the v4 branch already deleted; the test file works only because PHP doesn't error on unused `use` statements. Delete:

```php
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
```

- [ ] **Step 3: Remove 6 entries from the `$definitions` array**

Find this block (lines 38-54):

```php
$definitions = [
    AgeCheckDefinition::class,
    CountryOfOriginDefinition::class,
    CustomsCodeDefinition::class,
    DirectReturnDefinition::class,
    DisableDeliveryOptionsDefinition::class,
    FitInDigitalStampDefinition::class,
    FitInMailboxDefinition::class,
    HideSenderDefinition::class,
    InsuranceDefinition::class,
    LargeFormatDefinition::class,
    OnlyRecipientDefinition::class,
    PackageTypeDefinition::class,
    PriorityDeliveryDefinition::class,
    SameDayDeliveryDefinition::class,
    SignatureDefinition::class,
];
```

Replace with:

```php
$definitions = [
    AgeCheckDefinition::class,
    DirectReturnDefinition::class,
    HideSenderDefinition::class,
    InsuranceDefinition::class,
    LargeFormatDefinition::class,
    OnlyRecipientDefinition::class,
    PriorityDeliveryDefinition::class,
    SameDayDeliveryDefinition::class,
    SignatureDefinition::class,
];
```

- [ ] **Step 4: Regenerate the snapshot**

Run:

```bash
yarn test:unit:snapshot 2>&1 | tail -50
echo "exit: $?"
```

Expected: snapshot test updates from 15 entries to 9 entries.

- [ ] **Step 5: Verify the snapshot file content (sanity)**

Find the regenerated snapshot file (Pest stores them under `tests/__snapshots__/` or a path mirroring the test):

```bash
fd 'OrderOptionDefinitionInterfaceTest.*snapshots_all_definitions' tests/ | head -5
```

Open the file (whichever path Pest emitted) and confirm exactly 9 entries with no references to the 6 deleted Definitions. Spot-check `customs code` / `country of origin` / `package type` / `fit in mailbox` / `fit in digital stamp` / `disable delivery options` are absent.

- [ ] **Step 6: Run the affected test file**

Run:

```bash
docker compose run --rm php composer test -- --filter=OrderOptionDefinitionInterface 2>&1 | tail -30
echo "exit: $?"
```

Expected: pass.

- [ ] **Step 7: No commit yet.** Continue.

---

## Task 5: Refactor `tests/Unit/App/Order/Model/PdkProductTest.php` to use property keys

**Files:**

- Modify: `tests/Unit/App/Order/Model/PdkProductTest.php`

This test currently parameterizes via Definition class refs to look up the property key. After the cleanup, 3 of those Definitions are gone. Refactor the test to take the property key string directly — that's the production-equivalent path (`CartCalculationService` and similar consumers all use direct property/key access on `ProductSettings`).

- [ ] **Step 1: Remove the 3 dead Definition imports + 1 unused (SignatureDefinition will be replaced too)**

Delete these lines:

```php
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
```

Also delete:

```php
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
```

(no longer needed once we pass string keys instead of Definitions).

- [ ] **Step 2: Refactor `createNestedProducts` to take a string key**

Find this function (lines 20-46):

```php
function createNestedProducts(
    OrderOptionDefinitionInterface $definition,
                                   $value1,
                                   $value2,
                                   $value3
): PdkProduct {
    $key = $definition->getProductSettingsKey();

    $product = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt')
        ->withSettings(factory(ProductSettings::class)->with([$key => $value1]))
        ->store();

    $productLevel2 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew')
        ->withParent($product)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value2]))
        ->store();

    $productLevel3 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew--red')
        ->withParent($productLevel2)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value3]))
        ->store();

    return $productLevel3->make();
}
```

Replace with:

```php
function createNestedProducts(
    string $key,
           $value1,
           $value2,
           $value3
): PdkProduct {
    $product = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt')
        ->withSettings(factory(ProductSettings::class)->with([$key => $value1]))
        ->store();

    $productLevel2 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew')
        ->withParent($product)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value2]))
        ->store();

    $productLevel3 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew--red')
        ->withParent($productLevel2)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value3]))
        ->store();

    return $productLevel3->make();
}
```

- [ ] **Step 3: Refactor the `'merges parent settings correctly'` test**

Find this test (lines 48-63):

```php
it('merges parent settings correctly', function (
    OrderOptionDefinitionInterface $definition,
    int                            $value1,
    int                            $value2,
    int                            $value3,
    int                            $result
) {
    $product = createNestedProducts($definition, $value1, $value2, $value3);
    $key     = $definition->getProductSettingsKey();

    expect($product->mergedSettings->getAttribute($key))->toEqual($result);
})
    ->with([
        'signature' => new SignatureDefinition(),
    ])
    ->with('triState3');
```

Replace with:

```php
it('merges parent settings correctly', function (
    string $key,
    int    $value1,
    int    $value2,
    int    $value3,
    int    $result
) {
    $product = createNestedProducts($key, $value1, $value2, $value3);

    expect($product->mergedSettings->getAttribute($key))->toEqual($result);
})
    ->with([
        'signature' => 'signature',
    ])
    ->with('triState3');
```

- [ ] **Step 4: Refactor the `'calculates other options for child products'` test**

Find this test (lines 71-138):

```php
it('calculates other options for child products', function (string $definitionClass, $input, $output) {
    /** @var OrderOptionDefinitionInterface $definition */
    $definition = new $definitionClass();

    $product = createNestedProducts($definition, ...$input);

    expect($product->mergedSettings->getAttribute($definition->getProductSettingsKey()))->toBe($output);
})->with([
    'country of origin: NL, BE, DE -> DE' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => ['NL', 'BE', 'DE'],
        'output'     => 'DE',
    ],

    'country of origin: DE, -1, -1 -> DE' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => ['DE', -1, -1],
        'output'     => 'DE',
    ],

    'country of origin: -1, FR, -1 -> FR' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => [-1, 'FR', -1],
        'output'     => 'FR',
    ],

    'country of origin: -1, -1, NL -> NL' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => [-1, -1, 'NL'],
        'output'     => 'NL',
    ],

    'customs code: a, b, _ -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => ['a', 'b', -1],
        'output'     => 'b',
    ],

    'customs code: a, _, b -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => ['a', -1, 'b'],
        'output'     => 'b',
    ],

    'customs code: _, a, b -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => [-1, 'a', 'b'],
        'output'     => 'b',
    ],

    'package type: package, mailbox, _ -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['package', 'mailbox', '-1'],
        'output'     => 'mailbox',
    ],

    'package type: package, _, mailbox -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['package', '-1', 'mailbox'],
        'output'     => 'mailbox',
    ],

    'package type: _, package, mailbox -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['-1', 'package', 'mailbox'],
        'output'     => 'mailbox',
    ],
]);
```

Replace with:

```php
it('calculates other options for child products', function (string $key, $input, $output) {
    $product = createNestedProducts($key, ...$input);

    expect($product->mergedSettings->getAttribute($key))->toBe($output);
})->with([
    'country of origin: NL, BE, DE -> DE' => [
        'key'    => 'countryOfOrigin',
        'input'  => ['NL', 'BE', 'DE'],
        'output' => 'DE',
    ],

    'country of origin: DE, -1, -1 -> DE' => [
        'key'    => 'countryOfOrigin',
        'input'  => ['DE', -1, -1],
        'output' => 'DE',
    ],

    'country of origin: -1, FR, -1 -> FR' => [
        'key'    => 'countryOfOrigin',
        'input'  => [-1, 'FR', -1],
        'output' => 'FR',
    ],

    'country of origin: -1, -1, NL -> NL' => [
        'key'    => 'countryOfOrigin',
        'input'  => [-1, -1, 'NL'],
        'output' => 'NL',
    ],

    'customs code: a, b, _ -> b' => [
        'key'    => 'customsCode',
        'input'  => ['a', 'b', -1],
        'output' => 'b',
    ],

    'customs code: a, _, b -> b' => [
        'key'    => 'customsCode',
        'input'  => ['a', -1, 'b'],
        'output' => 'b',
    ],

    'customs code: _, a, b -> b' => [
        'key'    => 'customsCode',
        'input'  => [-1, 'a', 'b'],
        'output' => 'b',
    ],

    'package type: package, mailbox, _ -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['package', 'mailbox', '-1'],
        'output' => 'mailbox',
    ],

    'package type: package, _, mailbox -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['package', '-1', 'mailbox'],
        'output' => 'mailbox',
    ],

    'package type: _, package, mailbox -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['-1', 'package', 'mailbox'],
        'output' => 'mailbox',
    ],
]);
```

- [ ] **Step 5: Verify the property keys exist on `ProductSettings`**

Run:

```bash
rg "FIT_IN_MAILBOX|PACKAGE_TYPE|COUNTRY_OF_ORIGIN|CUSTOMS_CODE|SIGNATURE" src/Settings/Model/ProductSettings.php | head -10
```

Expected: each of `countryOfOrigin`, `customsCode`, `packageType`, `signature` exists as a property/const on `ProductSettings` (or its parent `AbstractMergeableModel`). If any are missing, **stop** — the test won't work with that key.

- [ ] **Step 6: Run the affected test file**

Run:

```bash
docker compose run --rm php composer test -- --filter=PdkProduct 2>&1 | tail -30
echo "exit: $?"
```

Expected: tests pass with the same data set count.

- [ ] **Step 7: No commit yet.** Continue.

---

## Task 6: Delete the 6 Definition class files

**Files:**

- Delete: `src/App/Options/Definition/CountryOfOriginDefinition.php`
- Delete: `src/App/Options/Definition/CustomsCodeDefinition.php`
- Delete: `src/App/Options/Definition/DisableDeliveryOptionsDefinition.php`
- Delete: `src/App/Options/Definition/FitInDigitalStampDefinition.php`
- Delete: `src/App/Options/Definition/FitInMailboxDefinition.php`
- Delete: `src/App/Options/Definition/PackageTypeDefinition.php`

- [ ] **Step 1: Delete all 6 files**

Run:

```bash
git rm src/App/Options/Definition/CountryOfOriginDefinition.php \
       src/App/Options/Definition/CustomsCodeDefinition.php \
       src/App/Options/Definition/DisableDeliveryOptionsDefinition.php \
       src/App/Options/Definition/FitInDigitalStampDefinition.php \
       src/App/Options/Definition/FitInMailboxDefinition.php \
       src/App/Options/Definition/PackageTypeDefinition.php
```

Expected: `git status` shows 6 deletions staged.

- [ ] **Step 2: Verify zero remaining references anywhere**

Run:

```bash
rg 'CountryOfOriginDefinition|CustomsCodeDefinition|DisableDeliveryOptionsDefinition|FitInDigitalStampDefinition|FitInMailboxDefinition|PackageTypeDefinition' src/ tests/
```

Expected: **no output**. If any hits remain, the test updates in Tasks 2-5 missed something — investigate before continuing.

- [ ] **Step 3: No commit yet.** Continue.

---

## Task 7: Final verification

**Files:**

- No edits.

- [ ] **Step 1: Run the full test suite**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log
echo "exit: $?"
```

Expected: tests pass. Compare summary line counts with `/tmp/baseline-tests.log`:

- Test count likely decreased by ~17 (6 + 5 + 6 = 17 dataset rows removed; some grouped tests have fewer passes now).
- No new FAIL entries — only the expected reduction.

- [ ] **Step 2: Run PHPStan**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log
echo "exit: $?"
```

Expected: zero errors. PHPStan should report no missing-class errors (we removed all references).

- [ ] **Step 3: Confirm no plugin breakage**

Re-run the plugin-side check from Task 1 Step 5:

```bash
for c in CountryOfOriginDefinition CustomsCodeDefinition DisableDeliveryOptionsDefinition FitInDigitalStampDefinition FitInMailboxDefinition PackageTypeDefinition; do
  hits=$(rg -l "\b$c\b" ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null | wc -l | tr -d ' ')
  echo "$c → $hits plugin file refs"
done
```

Expected: still all `0`.

- [ ] **Step 4: Snapshot diff sanity check**

Run:

```bash
git diff --stat tests/__snapshots__/
```

Expected: exactly one snapshot file changed (`OrderOptionDefinitionInterfaceTest__it_snapshots_all_definitions`); diff shows 6 entries removed.

- [ ] **Step 5: No commit yet.** Continue to Task 8.

---

## Task 8: Commit

**Files:**

- Stage: all changes from Tasks 2-7 (the 6 deletions are already staged from Task 6 Step 1; modified test files need staging).

- [ ] **Step 1: Show the diff to the user**

Run:

```bash
git add tests/Datasets/shipmentOptions.php \
        tests/Unit/App/Options/Helper/ProductSettingsDefinitionHelperTest.php \
        tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php \
        tests/Unit/App/Order/Model/PdkProductTest.php \
        tests/__snapshots__/
git diff --staged --stat
git status --short
```

Wait for user approval.

- [ ] **Step 2: Commit (only after approval)**

Run:

```bash
git commit -m "$(cat <<'EOF'
chore(options): drop 6 unregistered OrderOptionDefinition classes

Removes CountryOfOriginDefinition, CustomsCodeDefinition,
DisableDeliveryOptionsDefinition, FitInDigitalStampDefinition,
FitInMailboxDefinition, and PackageTypeDefinition — six inert classes that
were never registered in orderOptionDefinitions config and had no
production references. The product-only concepts they represented
(country of origin, customs code, fit-in-mailbox, package type, etc.)
remain alive via hand-declared ProductSettings::* properties consumed
directly by CartCalculationService and similar — no behavior change.

Tests updated to reference property-key strings directly instead of
Definition class refs, mirroring how production code accesses these
settings. Drops 17 dataset rows + 1 helper function + 1 dataset
registration + 8 dead use statements; regenerates one snapshot.

Audit reference: docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Schema A-1..A-6 and B-1).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Verify the commit**

Run:

```bash
git log -1 --stat
```

Expected: 6 deleted files + 4 modified test files + 1 modified snapshot.

- [ ] **Step 4: Plan complete.** Ready for cross-pattern review or PR creation (deferred until all per-pattern plans land).

---

## Roll-back instructions (if needed)

```bash
git revert HEAD
```

The change is a single commit; revert restores the 6 Definition classes and reverts test changes. No DB state, no plugin coordination.

---

## Why this is safe

- The 6 classes are zero-functionality stubs: they extend `AbstractOrderOptionDefinition` but never appear in `config/pdk-business-logic.php` `orderOptionDefinitions`, so PDK's helper chain never iterates over them.
- Plugin scan (PrestaShop + WooCommerce) returns 0 references to any of the 6 classes.
- The concepts they represent (country of origin, customs code, fit-in-mailbox, etc.) live on `ProductSettings` as hand-declared properties, consumed directly by `CartCalculationService` and similar. The cleanup does not touch those paths.
- Test rewrites preserve coverage: tests that exercised the helper through these dead Definitions had test cases removed (they were tautological — testing INHERIT defaults via classes that don't participate in option resolution). Tests that used Definition refs to look up property keys (`PdkProductTest`) get rewritten to pass the property key string directly — same coverage, fewer indirections.
- The `validate()` method that 8 Definitions override is unaffected by this plan; its removal is covered by the separate [validate() method removal plan](2026-05-11-validate-method-removal-plan.md) which depends on this plan landing first.
