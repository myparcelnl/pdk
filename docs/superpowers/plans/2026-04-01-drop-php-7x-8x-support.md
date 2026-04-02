# Drop PHP < 8.1 Support — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove PHP 7.4 and 8.0 support, making PHP 8.1 the minimum version. Clean up compatibility workarounds introduced during the PHP 7.4–8.5 matrix testing effort.

**Architecture:** Bump the minimum PHP version in composer.json, remove backwards-compatibility workarounds, adopt PHP 8.1 language features where they improve clarity, and update CI/Docker defaults.

**Tech Stack:** PHP 8.1+, Composer, Docker, GitHub Actions, Pest, PHPStan, Rector

---

## Context: Workarounds from PHP 7.4–8.5 compatibility session

During the multi-version compatibility work (April 2026, branch `feat/php-matrix-testing-and-support`), several workarounds were added to maintain PHP 7.4 backwards compatibility. When PHP < 8.1 support is dropped, these should be cleaned up:

### 1. Mock repository constructor parameter order

**Files:** `tests/Bootstrap/MockPdkOrderNoteRepository.php`, `MockPdkOrderRepository.php`, `MockPdkProductRepository.php`, `MockSettingsRepository.php`

**What was done:** Swapped constructor parameter order (storage first, optional data second) to fix PHP 8.0 deprecation of required-after-optional parameters. DI bindings were changed from `->constructor($data)` to `->constructorParameter('dataParam', $data)`.

**When dropping 7.4/8.0:** No action needed — the fix is clean and idiomatic. Keep as-is.

### 2. PdkCart dynamic property workaround

**File:** `src/App/Cart/Model/PdkCart.php`

**What was done:** `updateShippingMethod()` writes to `$this->attributes['shippingMethod']` directly instead of `$this->shippingMethod` to avoid PHP 8.2 dynamic property creation deprecation. The `setShippingMethodAttribute` mutator stores the incoming value in attributes, then calls `updateShippingMethod()` which overwrites it with the calculated result.

**When dropping 7.4/8.0:** Consider refactoring to use PHP 8.2 `readonly` properties or adding `#[\AllowDynamicProperties]` if appropriate. The current `$this->attributes['shippingMethod']` approach bypasses the model's `__set`/`setAttribute` pipeline — a cleaner fix might be possible with PHP 8.1 features. At minimum, add a `@TODO` pointing to this plan.

### 3. Vendor deprecation suppression bootstrap

**Files:** `tests/bootstrap.php`, `composer.json` (test:unit script)

**What was done:** Created `tests/bootstrap.php` that suppresses `E_DEPRECATED` globally on PHP 8.4+ via `error_reporting(E_ALL & ~E_DEPRECATED)`. Loaded via `auto_prepend_file` in the composer test:unit script. This is needed because Pest v1, PHP-DI, Symfony, and other vendor packages trigger implicitly-nullable-parameter deprecations at file-parse time on PHP 8.4+.

**When dropping 7.4/8.0:** This workaround remains necessary as long as Pest v1 is used. The real fix is upgrading to **Pest v2** (requires PHP 8.1+, PHPUnit 10). See Task 3 below.

### 4. ZipService error suppression

**File:** `src/Base/Service/ZipService.php`

**What was done:** Added `@` operator to `$this->currentFile->addFile()` because PHP 8.0+ emits a warning before returning false for non-existent files. The `@` suppresses the warning so our own `ZipException` is thrown instead.

**When dropping 7.4/8.0:** No action needed — this is the correct approach for ZipArchive across all PHP versions. Keep as-is.

### 5. Null/type guards added for PHP 8.0 strictness

**Files:** Multiple source files (see commit `d7d29645`)

Key locations:

- `src/App/Order/Collection/PdkOrderCollection.php` — null check before `->uuid` access
- `src/Account/Response/GetShopCarrierConfigurationsResponse.php` — `?? null` for missing `carrier_id`
- `src/Shipment/Service/DropOffService.php` — `!empty()` guard on array key
- `src/Shipment/Concern/EncodesCustomsDeclaration.php` — null check on `$shipment->recipient`
- `src/App/Cart/Service/CartCalculationService.php` — `is_object()` + null guards in closures
- `src/Shipment/Request/GetShipmentsRequest.php` — `InvalidArgumentException` for non-scalar IDs

**When dropping 7.4/8.0:** These guards are good defensive code. Keep them. But some can be simplified using PHP 8.1 features (see Task 5).

---

## File Map

| File                                 | Action | Responsibility                                                |
| ------------------------------------ | ------ | ------------------------------------------------------------- |
| `composer.json`                      | Modify | Bump `require.php` to `>=8.1`, update `platform.php` to `8.1` |
| `Dockerfile`                         | Modify | Change default `ARG PHP_VERSION=8.1`                          |
| `docker-compose.yml`                 | Modify | Change default fallback to `8.1`                              |
| `.env.template`                      | Modify | Change default `PHP_VERSION=8.1`                              |
| `.github/workflows/push.yml`         | Modify | Remove 7.4 and 8.0 from matrix                                |
| `.github/workflows/pull-request.yml` | Modify | Remove 7.4 and 8.0 from matrix                                |
| `composer.json` scripts              | Modify | Upgrade Pest to v2, PHPUnit to 10                             |
| `tests/bootstrap.php`                | Modify | May be removable after Pest v2 upgrade                        |
| `tests/Pest.php`                     | Modify | Adapt to Pest v2 API changes                                  |
| `phpunit.xml`                        | Modify | Adapt to PHPUnit 10 schema                                    |
| `src/**/*.php`                       | Modify | Adopt PHP 8.1 features where appropriate                      |
| `tests/**/*.php`                     | Modify | Adapt to Pest v2 / PHPUnit 10 API                             |

---

### Task 1: Bump minimum PHP version

**Files:**

- Modify: `composer.json`
- Modify: `Dockerfile`
- Modify: `docker-compose.yml`
- Modify: `.env.template`
- Modify: `README.md`

- [ ] **Step 1: Update composer.json PHP constraint**

Change `"php": ">=7.4.0"` to `"php": ">=8.1.0"` in `require`. Change `"php": "7.4"` to `"php": "8.1"` in `config.platform`.

- [ ] **Step 2: Update Docker defaults**

In `Dockerfile`, change `ARG PHP_VERSION=7.4` to `ARG PHP_VERSION=8.1`.

In `docker-compose.yml`, change both `${PHP_VERSION:-7.4}` fallbacks to `${PHP_VERSION:-8.1}`.

In `.env.template`, change `PHP_VERSION=7.4` to `PHP_VERSION=8.1`.

In `README.md`, update the "Requirements" section and the "Testing on a specific PHP version" section to reflect 8.1 as default.

- [ ] **Step 3: Update CI matrix**

In `.github/workflows/push.yml` and `.github/workflows/pull-request.yml`, remove `'7.4'` and `'8.0'` from the `php-version` matrix array.

- [ ] **Step 4: Run composer update and verify tests**

```bash
docker compose build
docker compose run php composer update --no-progress
docker compose run php composer test:unit
```

- [ ] **Step 5: Commit**

```bash
git add composer.json Dockerfile docker-compose.yml .env.template README.md .github/
git commit -m "feat!: drop PHP 7.4 and 8.0 support, require PHP 8.1+"
```

---

### Task 2: Run Rector to adopt PHP 8.1 language features

**Files:**

- Modify: `rector.php`
- Modify: `src/**/*.php`

- [ ] **Step 1: Update Rector config to target PHP 8.1**

In `rector.php`, add PHP 8.1 level set:

```php
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    // ... existing config ...
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
};
```

- [ ] **Step 2: Run Rector in dry-run mode**

```bash
docker compose run php composer quality
```

Review the proposed changes. Key transformations to expect:

- `readonly` properties where applicable
- Enum adoption where constants are used as enums
- Intersection types in type hints
- `never` return type
- `array_is_list()` usage
- First-class callable syntax `strlen(...)` instead of `'strlen'`

- [ ] **Step 3: Apply Rector changes**

```bash
docker compose run php composer quality:fix
```

- [ ] **Step 4: Run tests**

```bash
docker compose run php composer test:unit
```

- [ ] **Step 5: Review and commit**

Review the diff carefully. Revert any changes that reduce readability.

```bash
git add -A
git commit -m "refactor: adopt PHP 8.1 language features via Rector"
```

---

### Task 3: Upgrade Pest to v2

**Files:**

- Modify: `composer.json`
- Modify: `phpunit.xml`
- Modify: `tests/Pest.php`
- Modify: `tests/bootstrap.php`
- Modify: `tests/**/*.php` (potentially many files)

This is the largest task. Pest v2 requires PHPUnit 10 which has significant API changes.

- [ ] **Step 1: Update composer dependencies**

In `composer.json`, change:

- `"pestphp/pest": "^1.0.0"` → `"pestphp/pest": "^2.0"`
- `"spatie/pest-plugin-snapshots": "^1.0.0"` → check for v2-compatible version

```bash
docker compose run php composer update --no-progress
```

- [ ] **Step 2: Update phpunit.xml for PHPUnit 10**

PHPUnit 10 removed the `<coverage>` element and changed the schema. Update `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".cache/phpunit"
>
    <testsuites>
        <testsuite name="Test Suite">
            <directory>./tests/Unit</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
        <exclude>
            <file>./src/Api/Service/MyParcelApiService.php</file>
            <file>./src/Base/Concern/HasAttributes.php</file>
        </exclude>
    </source>
</phpunit>
```

- [ ] **Step 3: Update PHPUnit extensions**

PHPUnit 10 changed the extension API. Update `tests/Hook/ClearContainerCacheHook.php` and `tests/Hook/DeleteTemporaryFilesHook.php` to implement the new `PHPUnit\Runner\Extension\Extension` interface instead of the old hook interfaces.

- [ ] **Step 4: Update Pest.php for Pest v2**

Review Pest v2 migration guide. Key changes:

- `uses()->group()` syntax may change
- Custom expect helpers may need updating
- `->with()` dataset syntax may change

- [ ] **Step 5: Check if bootstrap.php is still needed**

If Pest v2 + its dependencies no longer emit `E_DEPRECATED`, remove `tests/bootstrap.php` and revert the `test:unit` script in `composer.json` back to just `"pest"`.

- [ ] **Step 6: Run tests and fix failures**

```bash
docker compose run php composer test:unit
```

Fix any API incompatibilities iteratively.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat!: upgrade to Pest v2 and PHPUnit 10"
```

---

### Task 4: Clean up PHP 8.1 null guard patterns

**Files:**

- Modify: `src/App/Cart/Service/CartCalculationService.php`
- Modify: `src/Shipment/Concern/EncodesCustomsDeclaration.php`
- Modify: `src/App/Order/Collection/PdkOrderCollection.php`

- [ ] **Step 1: Replace verbose null checks with nullsafe operator**

With PHP 8.1 as minimum, replace patterns like:

```php
// Before (PHP 7.4 compatible)
$shipment->recipient ? $shipment->recipient->cc : null

// After (PHP 8.1+)
$shipment->recipient?->cc
```

Apply this to:

- `src/Shipment/Concern/EncodesCustomsDeclaration.php` — `$shipment->recipient?->cc`
- Other locations where ternary null checks exist for property access

- [ ] **Step 2: Review `is_object()` guards in CartCalculationService**

Check whether the `is_object($line)` guards in `CartCalculationService.php` closures are still needed. If the `PdkOrderLineCollection` always contains model objects on PHP 8.1+, these guards can be replaced with proper type hints in the closure signature.

- [ ] **Step 3: Run tests**

```bash
docker compose run php composer test:unit
```

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "refactor: simplify null guards using PHP 8.1 nullsafe operator"
```

---

### Task 5: Update PHPStan baseline

**Files:**

- Modify: `phpstan.neon.dist`
- Modify: `phpstan-baseline.php`

- [ ] **Step 1: Regenerate PHPStan baseline**

With PHP 8.1 as the analysis target, some existing baseline entries may be resolvable and new ones may appear.

```bash
docker compose run php php -dmemory_limit=-1 vendor/bin/phpstan analyse --generate-baseline --no-progress
```

- [ ] **Step 2: Review changes to baseline**

Check if any new errors appeared. Fix real issues, add legitimate ones to baseline.

- [ ] **Step 3: Run PHPStan**

```bash
docker compose run php php -dmemory_limit=-1 vendor/bin/phpstan analyse --no-progress
```

- [ ] **Step 4: Commit**

```bash
git add phpstan-baseline.php phpstan.neon.dist
git commit -m "chore: regenerate PHPStan baseline for PHP 8.1"
```

---

### Task 6: Final verification

- [ ] **Step 1: Run full test suite on all supported versions**

```bash
for v in 8.1 8.2 8.3 8.4 8.5; do
  echo "=== PHP $v ==="
  PHP_VERSION=$v docker compose run php composer update --no-progress --no-scripts --no-plugins 2>&1 | tail -1
  PHP_VERSION=$v docker compose run php composer test:unit 2>&1 | grep "Tests:" | head -1
done
```

All versions must pass.

- [ ] **Step 2: Run PHPStan**

```bash
docker compose run php php -dmemory_limit=-1 vendor/bin/phpstan analyse --no-progress
```

- [ ] **Step 3: Commit any remaining fixes and create PR**

```bash
git push -u origin feat/drop-php-7x-8x-support
gh pr create --title "feat!: drop PHP 7.4/8.0 support, require PHP 8.1+" --body "..."
```
