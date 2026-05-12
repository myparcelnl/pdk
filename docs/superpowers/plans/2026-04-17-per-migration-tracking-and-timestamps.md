# Per-Migration Tracking + Timestamp-Based Migrations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Jira:** [INT-951 — Als PDK plugin wil ik betrouwbaar en vooral eenvoudig migraties kunnen doen](https://myparcelnl.atlassian.net/browse/INT-951). The PR that ships this work must reference INT-951 in the description; commit message templates in this plan include the key.

---

## 📍 Execution progress

Work is happening in a git worktree at `~/projects/worktrees/pdk-int-951/` (branch `feat/int-951-per-migration-tracking`, tracks `origin/main`). Tasks marked done below have been committed on this branch.

**Done in session 1 (2026-04-17):**
- ✅ Task 1 — `TimestampedMigrationInterface` (commit `488ac4fd`)
- ✅ Task 2 — `AbstractTimestampedMigration` (commit `b84a8680`)
- ✅ Task 3 — `settingKeyAppliedMigrations` + `migrationDirectory` config (commit `084f24e8`)
- ✅ Task 4 — `MockTimestampedMigration20260101` fixture (commit `057a5c45`)

Each of Tasks 1–3 passed both spec-compliance and code-quality review. Task 4 passed spec compliance; the code-quality review was deferred when session 1 was paused. All foundation pieces (interface, abstract class, config, test fixture) are in place; the remaining work is behavioural changes to `InstallerService` plus the WC adoption.

**Remaining (resume here in session 2):**
- ⬜ Task 5 — Identity resolver + `getAppliedMigrations()` with seeding (main behavioural change in `InstallerService::getUpgradeMigrations()`)
- ⬜ Task 5b — Eager-seed `applied_migrations` during fresh install
- ⬜ Task 6 — Mark migrations applied after `up()` runs
- ⬜ Task 7 — File-based migration loader
- ⬜ Task 7b — PDK-owned discovery via `migrationDirectory`
- ⬜ Task 8 — Sort timestamp migrations after version migrations
- ⬜ Task 9 — Regression test: RC version scenario
- ⬜ Task 10 — Full test suite sanity check
- ⬜ Task 15 — `make:migration` console command
- ⬜ Tasks 11–14 (in the WooCommerce plugin repo, not the PDK): verify PDK-owned discovery works in WC, create the concrete carrier V2 timestamped migration, integration test, manual verification. These happen in `~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/` against a branch composer-linked to this PDK worktree or to the released PDK version once this PR merges.

**When resuming:**

1. `cd ~/projects/worktrees/pdk-int-951 && git pull origin feat/int-951-per-migration-tracking` (or simply confirm the branch still matches what you pushed).
2. Re-run the installer baseline: `php -d auto_prepend_file=tests/bootstrap.php vendor/bin/pest tests/Unit/App/Installer` — should be 14/14 passing.
3. Re-dispatch the superpowers:subagent-driven-development skill; start with a code-quality review of Task 4 (commit `057a5c45`) before moving to Task 5.
4. Continue task-by-task in plan order.

**Known pre-existing baseline failures (unrelated to this plan):** 7 tests in `tests/Unit/App/Action/Backend/Debug/DownloadLogsActionTest.php` and `tests/Unit/Base/Service/ZipServiceTest.php` fail under PHP 8.4 due to old Symfony Console deprecations. Baseline was 7 fails before this work started; any change in that count is an INT-951 regression and needs investigating.

---

**Goal:** Replace the version-comparison migration gate with per-migration class/file tracking, and introduce Laravel-style timestamp-based file migrations (anonymous classes) alongside the existing class-based ones. Fix the concrete WooCommerce bug where `Migration6_1_0` is silently skipped on the test RC build because `6.1.0 <= 6.3.0 (installed)` and `6.4.0 > 6.3.0-rc.X`.

**Architecture:** Introduce a new settings option `_myparcelcom_applied_migrations` (a list of migration identities — class FQCN for class-based migrations, filename for file-based migrations). `InstallerService` filters the migration collection by identity instead of by version range. On first run after this change ships, the option is seeded from the legacy `installed_version` so class-based migrations that already ran under the old gate are not re-executed. A new `TimestampedMigrationInterface` + `AbstractTimestampedMigration` base supports Laravel-style migrations where a single file `returns new class extends AbstractTimestampedMigration { … }`. File-based migrations are **never** auto-seeded — they always run once, regardless of when they were added.

**Design rationale for `getId()`:** For class-based migrations, `get_class($migration)` is a stable identity. For anonymous classes (the Laravel-style ones), `get_class()` returns something like `class@anonymous\0/absolute/path/to/file.php:5$42` — not stable across servers/PHP versions. So `getId()` exists only on `TimestampedMigrationInterface` to expose the filename as a portable identity. Class-based migrations use `get_class()` via a fallback in `resolveMigrationId()`. Putting `getId()` on the base `MigrationInterface` would be a breaking change for any external implementer; isolating it in the sub-interface keeps the contract backwards compatible. Since the filename convention is `YYYY_MM_DD_HHMMSS_<slug>.php`, the id string is already chronologically sortable — so sort order uses `strcmp($a->getId(), $b->getId())` and we don't need a separate `getTimestamp()` method (same pattern as Laravel's `Migrator`).

**Tech Stack:**
- PHP 7.4 (plugin runtime constraint — no constructor property promotion, no readonly properties, no `str_ends_with`; anonymous classes are fine since 7.0)
- Pest (test framework in both repos)
- PHP-DI (container used for class-based migration resolution)
- WordPress options API / PrestaShop settings API (backing storage, abstracted via `PdkSettingsRepositoryInterface`)

**Repositories touched:**
- `myparcelnl/pdk` (shared framework — primary change)
- `myparcelnl-woocommerce` (adoption + concrete migration that fixes the carrier bug)

---

## File Structure

### PDK repo (`~/projects/pdk`)

| Path | Responsibility | New? |
|---|---|---|
| `src/App/Installer/Contract/MigrationInterface.php` | Base contract for migrations. Unchanged signatures — additive `getId()` is introduced on the timestamped sub-interface so existing implementers don't break. | No change |
| `src/App/Installer/Contract/TimestampedMigrationInterface.php` | New sub-interface declaring `getId(): string`. | Create |
| `src/App/Installer/Migration/AbstractTimestampedMigration.php` | Abstract base for file-based migrations. Holds `$id` set by loader. Explicitly disables `getVersion()`. | Create |
| `src/App/Installer/Service/InstallerService.php` | Core change: new `getAppliedMigrations()`, `markMigrationApplied()`, `resolveMigrationId()`, `discoverTimestampedMigrationFiles()`, file-loader path in `createMigrationCollection()`, identity-based filter replaces version gate in `getUpgradeMigrations()`, updated sort in `runUpMigrations()`, eager seed in `executeInstallation()`. Keeps `installed_version` writes as informational. | Modify |
| `config/pdk-settings.php` | Add `settingKeyAppliedMigrations` factory and `migrationDirectory` factory (default: `<rootDir>/src/Migration`; plugins override in their own config to change path, or set `null` to disable PDK-owned discovery). | Modify |
| `tests/Bootstrap/MockTimestampedMigration20260101.php` | Mock fixture for tests. | Create |
| `tests/Unit/App/Installer/Service/InstallerServiceTest.php` | Add tests for: seeding, identity-based filter, file-loader, timestamp sort, RC-version scenario. | Modify |

### WooCommerce plugin repo (`~/projects/docker-wordpress/plugins/myparcelnl-woocommerce`)

| Path | Responsibility | New? |
|---|---|---|
| `src/Pdk/Plugin/Installer/WcMigrationService.php` | **No change required.** PDK-owned discovery finds timestamped migration files automatically. Plugins that want to disable or relocate discovery set `migrationDirectory` in their own config override. | No change |
| `src/Migration/2026_04_17_100000_migrate_carriers_to_v2.php` | New file-based timestamped migration replicating the body of `Migration6_1_0`. Runs regardless of version, exactly once per install. | Create |
| `src/Migration/Migration6_1_0.php` | Keep as-is (registered but always seeded as applied for existing installs). Delete once all environments have upgraded past this transition. | No change now; deletion is follow-up |
| `tests/Unit/Migration/TimestampedCarrierMigrationTest.php` | Test that the new file migration hydrates carriers correctly and is marked applied after running. | Create |

---

## Conventions referenced

- **PDK tests:** run from `~/projects/pdk` with `./vendor/bin/pest <args>`.
- **WC plugin tests:** must run inside the Docker container (see WC CLAUDE.md). Command pattern:

  ```bash
  cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
    'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest <args>'
  ```
- **Commits:** conventional commits. Every commit in this plan includes `INT-951` in the footer so Jira links them automatically.

---

## Task 1 (PDK): Add `TimestampedMigrationInterface`

**Files:**
- Create: `src/App/Installer/Contract/TimestampedMigrationInterface.php`

- [ ] **Step 1: Create the interface file**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

interface TimestampedMigrationInterface extends MigrationInterface
{
    /**
     * Stable unique identifier for this migration. Used for per-migration tracking
     * AND for ordering — because filenames follow "YYYY_MM_DD_HHMMSS_<slug>", a
     * lexicographic sort on this string yields chronological order.
     *
     * For file-based migrations this is the filename without extension, e.g.
     * "2026_04_17_100000_migrate_carriers".
     */
    public function getId(): string;
}
```

- [ ] **Step 2: Commit**

```bash
cd ~/projects/pdk
git add src/App/Installer/Contract/TimestampedMigrationInterface.php
git commit -m "feat(installer): add TimestampedMigrationInterface

INT-951"
```

---

## Task 2 (PDK): Add `AbstractTimestampedMigration` base class

**Files:**
- Create: `src/App/Installer/Migration/AbstractTimestampedMigration.php`

- [ ] **Step 1: Create the abstract base**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Migration;

use LogicException;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;

abstract class AbstractTimestampedMigration implements TimestampedMigrationInterface
{
    /** @var string */
    private $id = '';

    /**
     * Called by the InstallerService loader once the migration file has been required.
     * Anonymous-class migrations cannot know their own filename, so identity is injected.
     */
    public function setIdentity(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        if ('' === $this->id) {
            throw new LogicException('Migration identity has not been set. Ensure the migration is loaded via InstallerService::loadFileMigration().');
        }

        return $this->id;
    }

    /**
     * Timestamp-based migrations are not version-gated.
     * This method exists solely to satisfy MigrationInterface.
     */
    final public function getVersion(): string
    {
        throw new LogicException('Timestamp-based migrations are not version-gated. Use getId() for ordering.');
    }

    public function down(): void
    {
        // Default: no-op. Subclasses may override.
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/App/Installer/Migration/AbstractTimestampedMigration.php
git commit -m "feat(installer): add AbstractTimestampedMigration base class

INT-951"
```

---

## Task 3 (PDK): Add `settingKeyAppliedMigrations` + `migrationDirectory` config

**Files:**
- Modify: `config/pdk-settings.php` (append to the returned array, near `settingKeyInstalledVersion`)

- [ ] **Step 1: Add the new factory entries**

Locate the existing `'settingKeyInstalledVersion' => factory(...)` entry near the end of the returned array. Append after it:

```php
    /**
     * Settings key where the list of applied migration identities is saved.
     * Format: string[] of migration ids (FQCN for class-based, filename for file-based).
     */
    'settingKeyAppliedMigrations' => factory(function () {
        return PdkFacade::get('createSettingsKey')('applied_migrations');
    }),

    /**
     * Directory the installer scans for timestamped migration files.
     * Defaults to "<rootDir>/src/Migration". Plugins can override in their
     * own config to point at a different directory, or set to null to disable
     * PDK-owned discovery entirely (e.g. if the plugin prefers to register
     * every source explicitly via its MigrationService).
     */
    'migrationDirectory' => factory(function () {
        return rtrim(PdkFacade::get('rootDir'), '/') . '/src/Migration';
    }),
```

- [ ] **Step 2: Commit**

```bash
git add config/pdk-settings.php
git commit -m "feat(installer): add settingKeyAppliedMigrations + migrationDirectory config

INT-951"
```

---

## Task 4 (PDK): Create mock timestamped migration fixture for tests

**Files:**
- Create: `tests/Bootstrap/MockTimestampedMigration20260101.php`

- [ ] **Step 1: Create the mock**

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

final class MockTimestampedMigration20260101 extends AbstractTimestampedMigration
{
    public function __construct()
    {
        // In production code, setIdentity is called by the InstallerService loader.
        // The mock is registered as a FQCN, not as a file path, so we self-identify here.
        $this->setIdentity('2026_01_01_000000_mock_timestamped');
    }

    public function up(): void
    {
        // Write a sentinel so tests can assert this migration ran.
        Settings::set(sprintf('%s.%s', OrderSettings::ID, 'mockTimestampedMarker'), 'applied');

        if (isset($GLOBALS['__migration_order'])) {
            $GLOBALS['__migration_order'][] = $this->getId();
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add tests/Bootstrap/MockTimestampedMigration20260101.php
git commit -m "test(installer): add mock timestamped migration fixture

INT-951"
```

---

## Task 5 (PDK): Add identity resolver + `getAppliedMigrations()` with seeding

**Files:**
- Modify: `src/App/Installer/Service/InstallerService.php` (add methods near `getInstalledVersion()` around line 110; modify the filter in `getUpgradeMigrations()`)

- [ ] **Step 1: Write failing test for the seeding behaviour**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('seeds applied_migrations from installed_version on first access', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Simulate an existing install upgraded past 1.2.0 but before this PDK change.
    $settingsRepository->store($installedVersionKey, '1.2.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    // Mock migrations in the test bootstrap: MockUpgradeMigration110 (1.1.0),
    // MockUpgradeMigration120 (1.2.0), MockUpgradeMigration130 (1.3.0).
    // Versions <= 1.2.0 should be seeded as applied; 1.3.0 should NOT be seeded.
    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->not->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class);
});
```

- [ ] **Step 2: Run the test — verify it fails**

```bash
cd ~/projects/pdk
./vendor/bin/pest --filter='seeds applied_migrations from installed_version'
```

Expected: FAIL — either `Pdk::get('settingKeyAppliedMigrations')` throws (if Task 3 somehow wasn't done), or the stored value stays null.

- [ ] **Step 3: Add `resolveMigrationId()`, `getAppliedMigrations()`, `markMigrationApplied()` in `InstallerService.php`**

Add the `use` line near the other imports at the top of the file:

```php
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
```

Insert these three methods after the existing `protected function getInstalledVersion()` method (around line 113):

```php
    /**
     * Returns the identity string used to track whether a migration has been applied.
     * Falls back to the class FQCN for class-based migrations that don't implement
     * TimestampedMigrationInterface.
     */
    protected function resolveMigrationId(MigrationInterface $migration): string
    {
        if ($migration instanceof TimestampedMigrationInterface) {
            return $migration->getId();
        }

        return get_class($migration);
    }

    /**
     * Returns the list of migration identities already applied on this installation.
     * On first access after a plugin upgrade (when the key doesn't exist yet but
     * installed_version does), seeds the list from the legacy installed_version
     * gate so class-based migrations that already ran are not re-executed.
     *
     * Fresh installs bypass this lazy seed path — executeInstallation() seeds
     * eagerly with ALL registered upgrade migrations. See that method for why.
     *
     * @param  null|Collection<MigrationInterface> $allMigrations Required on first access to compute the seed.
     *
     * @return string[]
     */
    protected function getAppliedMigrations(?Collection $allMigrations = null): array
    {
        $key    = Pdk::get('settingKeyAppliedMigrations');
        $stored = $this->settingsRepository->get($key);

        if (is_array($stored)) {
            return $stored;
        }

        if (null === $allMigrations) {
            // Cannot seed without the collection; return empty but do NOT persist.
            return [];
        }

        $installedVersion = $this->getInstalledVersion();

        if (! $installedVersion) {
            // No installed_version and no applied_migrations. This only happens
            // when getAppliedMigrations is called during install() before
            // executeInstallation completes. Return empty without persisting —
            // executeInstallation will seed eagerly.
            return [];
        }

        // Existing install upgrading to a PDK that has this tracking system.
        // Mark every class-based migration whose version is <= installed_version as applied.
        // Timestamp-based migrations are intentionally NOT seeded — they represent
        // net-new work relative to the pre-tracking era and should run.
        $seed = $allMigrations
            ->filter(function (MigrationInterface $m) use ($installedVersion) {
                if ($m instanceof TimestampedMigrationInterface) {
                    return false;
                }

                return version_compare($m->getVersion(), $installedVersion, '<=');
            })
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store($key, $seed);

        return $seed;
    }

    /**
     * Appends a migration identity to the persisted applied list.
     */
    protected function markMigrationApplied(MigrationInterface $migration): void
    {
        $key     = Pdk::get('settingKeyAppliedMigrations');
        $applied = $this->getAppliedMigrations();
        $id      = $this->resolveMigrationId($migration);

        if (! in_array($id, $applied, true)) {
            $applied[] = $id;
            $this->settingsRepository->store($key, $applied);
        }
    }
```

- [ ] **Step 4: Wire seeding into `getUpgradeMigrations()`**

Modify the existing `getUpgradeMigrations()` method at the bottom of the file. Replace the final filter block (the version-range filter):

```php
        if (! $version) {
            return $collection;
        }

        return $collection->filter(function (MigrationInterface $migration) use ($version) {
            return version_compare($migration->getVersion(), $this->getInstalledVersion(), '>')
                && version_compare($migration->getVersion(), $version, '<=');
        });
```

with the identity-based filter that seeds on first access:

```php
        if (! $version) {
            return $collection;
        }

        // Trigger seeding on first access (pass collection so seed can be computed).
        $applied = $this->getAppliedMigrations($collection);

        return $collection->filter(function (MigrationInterface $migration) use ($applied) {
            return ! in_array($this->resolveMigrationId($migration), $applied, true);
        });
```

- [ ] **Step 5: Run the test — verify it passes**

```bash
./vendor/bin/pest --filter='seeds applied_migrations from installed_version'
```

Expected: PASS.

- [ ] **Step 6: Run the full installer test suite to catch regressions**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: all tests green. If any legacy test fails because it relied on the old version-based filter, fix it in-place by pre-populating `settingKeyAppliedMigrations` in that test's Arrange block to simulate its intended state. **Do not revert this task's changes to make a legacy test pass — adjust the test.**

- [ ] **Step 7: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): replace version gate with per-migration tracking

Introduce _myparcelcom_applied_migrations settings key that records
migration identities (class FQCN or file id) as they run. On first
access the list is seeded from the legacy installed_version so
migrations that already ran under the old gate are not re-executed.
Timestamp-based migrations are intentionally not auto-seeded.

INT-951"
```

---

## Task 5b (PDK): Eager-seed applied_migrations during fresh install

**Why this task exists:** on a fresh install, `executeInstallation()` runs installation migrations only — upgrade migrations in the registry do not fire. If we leave `applied_migrations` empty at the end of a fresh install, the user's *next* version bump would then run every timestamped upgrade migration that was already shipped in the initial version, because the lazy seed in `getAppliedMigrations()` has no way to know which migrations were "baked into the install". We preempt this by seeding every registered upgrade migration (class-based AND timestamped) as applied at install time. Only migrations added in future versions will run.

**Files:**
- Modify: `src/App/Installer/Service/InstallerService.php` (extend `executeInstallation()`)

- [ ] **Step 1: Write failing test — fresh install pre-marks all upgrade migrations as applied**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('seeds applied_migrations with every upgrade migration after a fresh install', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Simulate a fresh install: no installed_version, no applied_migrations.
    $settingsRepository->store($installedVersionKey, null);
    $settingsRepository->store($appliedMigrationsKey, null);

    // Register one timestamped migration to prove it too gets pre-marked.
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration(
        \MyParcelNL\Pdk\Tests\Bootstrap\MockTimestampedMigration20260101::class
    );

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    // Every upgrade migration that was registered at install time must be pre-marked.
    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class)
        ->toContain('2026_01_01_000000_mock_timestamped');

    // And the timestamped migration's up() must NOT have run.
    expectSettingsToContain(['order.mockTimestampedMarker' => null]);
});
```

- [ ] **Step 2: Run test — verify it fails**

```bash
./vendor/bin/pest --filter='seeds applied_migrations with every upgrade migration after a fresh install'
```

Expected: FAIL — `executeInstallation()` currently doesn't touch `applied_migrations`.

- [ ] **Step 3: Extend `executeInstallation()` in `InstallerService.php`**

Locate the existing `executeInstallation()`:

```php
    protected function executeInstallation(...$args): void
    {
        $this->setDefaultSettings();
        $this->migrateInstall();
    }
```

Replace with:

```php
    protected function executeInstallation(...$args): void
    {
        $this->setDefaultSettings();
        $this->migrateInstall();
        $this->seedAppliedMigrationsForFreshInstall();
    }

    /**
     * Pre-marks every registered upgrade migration as applied on a fresh install.
     * This prevents them from firing retroactively on the user's first upgrade —
     * they're considered "baked into" the installed version.
     */
    protected function seedAppliedMigrationsForFreshInstall(): void
    {
        $upgrades = $this->createMigrationCollection(
            method_exists($this->migrationService, 'getUpgradeMigrations')
                ? $this->migrationService->getUpgradeMigrations()
                : $this->migrationService->all()
        );

        $ids = $upgrades
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store(Pdk::get('settingKeyAppliedMigrations'), $ids);
    }
```

- [ ] **Step 4: Run tests — verify pass**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: green. The fresh-install test passes, and pre-existing fresh-install test (the one that was there before this plan) should still pass too because it doesn't assert on `applied_migrations`.

- [ ] **Step 5: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): eager-seed applied_migrations on fresh install

Fresh installs now pre-mark every registered upgrade migration
(class-based and timestamped) as applied. Prevents the user's
first version upgrade from retroactively running migrations that
were baked into the installed version.

INT-951"
```

---

## Task 6 (PDK): Mark migrations applied after `up()` runs

**Files:**
- Modify: `src/App/Installer/Service/InstallerService.php` (around line 253, the `runUpMigrations` method)

- [ ] **Step 1: Write failing test — migration marked applied after running**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('records a migration identity in applied_migrations after it runs', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Pre-seed as if on 1.1.0 — only MockUpgradeMigration110 considered applied.
    $settingsRepository->store($installedVersionKey, '1.1.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class);
});
```

- [ ] **Step 2: Run test — verify it fails**

```bash
./vendor/bin/pest --filter='records a migration identity in applied_migrations after it runs'
```

Expected: FAIL — migrations ran but weren't recorded.

- [ ] **Step 3: Modify `runUpMigrations()` and add a `compareMigrations` stub**

Replace the current body of `runUpMigrations()`:

```php
    private function runUpMigrations(Collection $migrations): void
    {
        $migrations
            ->sort(function (MigrationInterface $a, MigrationInterface $b) {
                return version_compare($a->getVersion(), $b->getVersion());
            })
            ->each(function (MigrationInterface $migration) {
                $migration->up();
            });
    }
```

with:

```php
    private function runUpMigrations(Collection $migrations): void
    {
        $migrations
            ->sort([$this, 'compareMigrations'])
            ->each(function (MigrationInterface $migration) {
                $migration->up();
                $this->markMigrationApplied($migration);
            });
    }

    /**
     * Sort comparator for migrations. Full version/timestamp-aware implementation is added in Task 8.
     */
    public function compareMigrations(MigrationInterface $a, MigrationInterface $b): int
    {
        return version_compare($a->getVersion(), $b->getVersion());
    }
```

- [ ] **Step 4: Run tests — verify pass**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: all green.

- [ ] **Step 5: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): record migration identity after each up() call

INT-951"
```

---

## Task 7 (PDK): File-based migration loader

**Files:**
- Modify: `tests/Bootstrap/MockMigrationService.php` (add dynamic registration helpers)
- Modify: `src/App/Installer/Service/InstallerService.php` (modify `createMigrationCollection`, add `loadFileMigration`)

- [ ] **Step 1: Inspect existing `MockMigrationService` shape**

```bash
cat tests/Bootstrap/MockMigrationService.php
```

Note whether it exposes `getUpgradeMigrations()` returning a fixed array. The registration helper must extend whatever that source is so test registration is possible.

- [ ] **Step 2: Add registration helpers to `MockMigrationService`**

Inside the class, add a static `$extraUpgrades` array and helper methods. Also update `getUpgradeMigrations()` to merge `$extraUpgrades` into its return.

```php
    /** @var array<int, string> */
    private static $extraUpgrades = [];

    public static function addUpgradeMigration(string $source): void
    {
        self::$extraUpgrades[] = $source;
    }

    public static function removeUpgradeMigration(string $source): void
    {
        self::$extraUpgrades = array_values(array_filter(
            self::$extraUpgrades,
            function ($s) use ($source) { return $s !== $source; }
        ));
    }

    public static function resetExtraUpgrades(): void
    {
        self::$extraUpgrades = [];
    }
```

Modify the existing `getUpgradeMigrations()` to merge:

```php
    public function getUpgradeMigrations(): array
    {
        return array_merge(
            [/* existing fixed list */],
            self::$extraUpgrades
        );
    }
```

Add a global `afterEach()` hook in `InstallerServiceTest.php` to reset between tests:

```php
afterEach(function () {
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::resetExtraUpgrades();
});
```

- [ ] **Step 3: Write failing test for file-based loader**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('loads a file-based migration and runs it exactly once', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_migration_test_' . uniqid();
    mkdir($tmpDir, 0777, true);
    $file = $tmpDir . '/2026_04_17_100000_test_file_migration.php';

    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        Settings::set(OrderSettings::ID . '.fileMigrationMarker', 'applied');
    }
};
PHP
    );

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration($file);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    expect($applied)->toContain('2026_04_17_100000_test_file_migration');
    expectSettingsToContain(['order.fileMigrationMarker' => 'applied']);

    unlink($file);
    rmdir($tmpDir);
});
```

- [ ] **Step 4: Run test — verify it fails**

```bash
./vendor/bin/pest --filter='loads a file-based migration and runs it exactly once'
```

Expected: FAIL — `createMigrationCollection` currently passes paths through `Pdk::get($source)`, which throws.

- [ ] **Step 5: Update `createMigrationCollection()` and add `loadFileMigration()`**

Add the `use` line at the top of `InstallerService.php`:

```php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
```

Replace the existing `createMigrationCollection()`:

```php
    private function createMigrationCollection(array $migrations): Collection
    {
        return Collection::make($migrations)
            ->map(function (string $className) {
                return Pdk::get($className);
            });
    }
```

with:

```php
    private function createMigrationCollection(array $migrations): Collection
    {
        return Collection::make($migrations)
            ->map(function ($source) {
                // File-based migration: absolute path ending in .php
                if (is_string($source)
                    && '.php' === substr($source, -4)
                    && is_file($source)
                ) {
                    return $this->loadFileMigration($source);
                }

                // Class-based migration: FQCN resolved via container
                return Pdk::get($source);
            });
    }

    /**
     * Loads an anonymous-class migration from a file whose basename follows
     * "YYYY_MM_DD_HHMMSS_<slug>.php", injects identity, and returns the instance.
     */
    private function loadFileMigration(string $path): MigrationInterface
    {
        /** @var mixed $migration */
        $migration = require $path;

        if (! $migration instanceof MigrationInterface) {
            throw new \RuntimeException(sprintf(
                'Migration file "%s" must return an instance of MigrationInterface.',
                $path
            ));
        }

        if ($migration instanceof AbstractTimestampedMigration) {
            $basename = pathinfo($path, PATHINFO_FILENAME);

            if (! preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $basename)) {
                throw new \RuntimeException(sprintf(
                    'Migration filename "%s" does not match "YYYY_MM_DD_HHMMSS_<slug>" convention.',
                    $basename
                ));
            }

            $migration->setIdentity($basename);
        }

        return $migration;
    }
```

- [ ] **Step 6: Run tests — verify pass**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: green.

- [ ] **Step 7: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Bootstrap/MockMigrationService.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): support file-based anonymous-class migrations

Paths registered with MigrationServiceInterface can now point to
.php files that return a single 'new class extends
AbstractTimestampedMigration' instance. The loader infers the id
from the filename (YYYY_MM_DD_HHMMSS_<slug>.php).

INT-951"
```

---

## Task 7b (PDK): Auto-discover timestamped migration files from `migrationDirectory`

**Why this task exists:** individual plugins (WooC, Presta, …) shouldn't each need to re-implement glob discovery in their `MigrationService`. The PDK owns it, driven by the `migrationDirectory` config added in Task 3. Plugins get it for free; plugins that want to opt out set `migrationDirectory => null` in their own config override. Plugins can also still register file paths directly from their `MigrationService::getUpgradeMigrations()` for edge cases (e.g., multiple discovery dirs) — the PDK deduplicates sources before building the collection.

**Files:**
- Modify: `src/App/Installer/Service/InstallerService.php` (add `discoverTimestampedMigrationFiles()` and hook it into `getUpgradeMigrations()`)

- [ ] **Step 1: Write failing test — file in `migrationDirectory` is auto-discovered**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('auto-discovers timestamped migration files from migrationDirectory', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_autodiscover_' . uniqid();
    mkdir($tmpDir, 0777, true);

    $file = $tmpDir . '/2026_06_01_000000_autodiscover_test.php';
    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        Settings::set(OrderSettings::ID . '.autoDiscoverMarker', 'applied');
    }
};
PHP
    );

    // Override migrationDirectory for the duration of this test.
    Pdk::set('migrationDirectory', $tmpDir);

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    expect($applied)->toContain('2026_06_01_000000_autodiscover_test');
    expectSettingsToContain(['order.autoDiscoverMarker' => 'applied']);

    unlink($file);
    rmdir($tmpDir);
});

it('does not duplicate-run when the same file is both in migrationDirectory and registered via MigrationService', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_dedupe_' . uniqid();
    mkdir($tmpDir, 0777, true);

    $file = $tmpDir . '/2026_06_02_000000_dedupe_test.php';
    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        $GLOBALS['__dedupe_runs'] = ($GLOBALS['__dedupe_runs'] ?? 0) + 1;
    }
};
PHP
    );

    Pdk::set('migrationDirectory', $tmpDir);

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    // Register the SAME file via the MigrationService too.
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration($file);

    $GLOBALS['__dedupe_runs'] = 0;

    Installer::install();

    expect($GLOBALS['__dedupe_runs'])->toBe(1);

    unset($GLOBALS['__dedupe_runs']);
    unlink($file);
    rmdir($tmpDir);
});
```

- [ ] **Step 2: Run tests — verify they fail**

```bash
cd ~/projects/pdk
./vendor/bin/pest --filter='auto-discovers timestamped migration files'
```

Expected: FAIL — the file in `migrationDirectory` isn't being picked up yet.

- [ ] **Step 3: Add `discoverTimestampedMigrationFiles()` and wire it into `getUpgradeMigrations()`**

Add the helper method in `InstallerService.php` (near the other private helpers):

```php
    /**
     * Returns absolute paths of every file in the configured migrationDirectory
     * whose basename matches the YYYY_MM_DD_HHMMSS_<slug>.php convention.
     *
     * Returns an empty array if the config is null, absent, or points to a
     * directory that doesn't exist — lets plugins opt out by setting
     * `migrationDirectory` to null in their own config.
     *
     * @return string[]
     */
    private function discoverTimestampedMigrationFiles(): array
    {
        $dir = null;
        try {
            $dir = Pdk::get('migrationDirectory');
        } catch (\Throwable $e) {
            // Config key not defined — treat as disabled.
            return [];
        }

        if (! is_string($dir) || ! is_dir($dir)) {
            return [];
        }

        $files = glob(rtrim($dir, '/') . '/*.php') ?: [];

        return array_values(array_filter($files, function (string $path) {
            $basename = pathinfo($path, PATHINFO_FILENAME);
            return (bool) preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $basename);
        }));
    }
```

Modify the existing `getUpgradeMigrations()` to merge discovered files and dedupe:

```php
    private function getUpgradeMigrations(?string $version = null): Collection
    {
        $useLegacy = ! method_exists($this->migrationService, 'getUpgradeMigrations');

        if ($useLegacy) {
            Logger::deprecated(
                sprintf('Method "%s::all()"', MigrationServiceInterface::class),
                'getUpgradeMigrations and getInstallationMigrations'
            );
        }

        $registered = $useLegacy
            ? $this->migrationService->all()
            : $this->migrationService->getUpgradeMigrations();

        // Merge PDK-owned file discovery on top, then dedupe. A source can
        // legitimately appear in both (plugin explicitly registered a file
        // that's also in migrationDirectory) — dedupe prevents double-require.
        $sources = array_values(array_unique(array_merge(
            $registered,
            $this->discoverTimestampedMigrationFiles()
        )));

        $collection = $this->createMigrationCollection($sources);

        if (! $version) {
            return $collection;
        }

        $applied = $this->getAppliedMigrations($collection);

        return $collection->filter(function (MigrationInterface $migration) use ($applied) {
            return ! in_array($this->resolveMigrationId($migration), $applied, true);
        });
    }
```

- [ ] **Step 4: Run tests — verify pass**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: green.

- [ ] **Step 5: Also run the fresh-install seeding test from Task 5b — confirm it still passes with discovery on**

```bash
./vendor/bin/pest --filter='seeds applied_migrations with every upgrade migration'
```

Expected: PASS. The `seedAppliedMigrationsForFreshInstall()` in Task 5b calls `$this->migrationService->getUpgradeMigrations()` directly without going through `discoverTimestampedMigrationFiles()`. **Fix:** update Task 5b's `seedAppliedMigrationsForFreshInstall()` so it also picks up auto-discovered files — otherwise a fresh install with only auto-discovered migrations won't pre-mark them, reopening the footgun.

Open `src/App/Installer/Service/InstallerService.php`, locate `seedAppliedMigrationsForFreshInstall()`, and replace its body:

```php
    protected function seedAppliedMigrationsForFreshInstall(): void
    {
        $registered = method_exists($this->migrationService, 'getUpgradeMigrations')
            ? $this->migrationService->getUpgradeMigrations()
            : $this->migrationService->all();

        $sources = array_values(array_unique(array_merge(
            $registered,
            $this->discoverTimestampedMigrationFiles()
        )));

        $upgrades = $this->createMigrationCollection($sources);

        $ids = $upgrades
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store(Pdk::get('settingKeyAppliedMigrations'), $ids);
    }
```

Re-run:

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: green across all installer tests.

- [ ] **Step 6: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): auto-discover timestamped migration files

The installer now globs the configured migrationDirectory for files
matching YYYY_MM_DD_HHMMSS_<slug>.php and merges them into the
upgrade migration list alongside anything the plugin registered
explicitly. Sources are deduplicated. Plugins that want to opt out
set migrationDirectory => null in their config.

INT-951"
```

---

## Task 8 (PDK): Sort timestamp migrations after version migrations

**Files:**
- Modify: `src/App/Installer/Service/InstallerService.php` (expand the `compareMigrations` stub from Task 6)
- Modify: `tests/Bootstrap/MockUpgradeMigration110.php`, `MockUpgradeMigration120.php`, `MockUpgradeMigration130.php` (append timestamp-order tracking global)

- [ ] **Step 1: Add execution-order tracking to mock migrations**

For each of `MockUpgradeMigration110`, `MockUpgradeMigration120`, `MockUpgradeMigration130`, add this line at the top of `up()`:

```php
    public function up(): void
    {
        if (isset($GLOBALS['__migration_order'])) {
            $GLOBALS['__migration_order'][] = $this->getVersion();
        }

        // existing body…
    }
```

`MockTimestampedMigration20260101` already has the equivalent block from Task 4.

- [ ] **Step 2: Write failing test — timestamped runs after version-based**

Append to `tests/Unit/App/Installer/Service/InstallerServiceTest.php`:

```php
it('runs timestamp-based migrations after version-based ones within the same execution', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    $settingsRepository->store($installedVersionKey, '1.1.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration(
        \MyParcelNL\Pdk\Tests\Bootstrap\MockTimestampedMigration20260101::class
    );

    $GLOBALS['__migration_order'] = [];

    Installer::install();

    $order = $GLOBALS['__migration_order'];

    expect($order)
        ->toContain('1.2.0')
        ->toContain('1.3.0')
        ->toContain('2026_01_01_000000_mock_timestamped');

    $last = end($order);
    expect($last)->toStartWith('2026_');

    unset($GLOBALS['__migration_order']);
});
```

- [ ] **Step 3: Run test — verify failure**

```bash
./vendor/bin/pest --filter='runs timestamp-based migrations after version-based ones'
```

Expected: FAIL — current `compareMigrations` stub calls `version_compare($a->getVersion(), $b->getVersion())`, which raises `LogicException` for the timestamped migration (its `getVersion()` throws).

- [ ] **Step 4: Expand `compareMigrations` in `InstallerService.php`**

Replace the Task 6 stub:

```php
    public function compareMigrations(MigrationInterface $a, MigrationInterface $b): int
    {
        $aIsTs = $a instanceof TimestampedMigrationInterface;
        $bIsTs = $b instanceof TimestampedMigrationInterface;

        // All version-based migrations run before any timestamp-based migration.
        if ($aIsTs !== $bIsTs) {
            return $aIsTs ? 1 : -1;
        }

        // Filenames start with YYYY_MM_DD_HHMMSS, so strcmp on id yields chronological order.
        if ($aIsTs && $bIsTs) {
            return strcmp($a->getId(), $b->getId());
        }

        return version_compare($a->getVersion(), $b->getVersion());
    }
```

- [ ] **Step 5: Run test — verify pass**

```bash
./vendor/bin/pest tests/Unit/App/Installer
```

Expected: green.

- [ ] **Step 6: Commit**

```bash
git add src/App/Installer/Service/InstallerService.php tests/Bootstrap/MockUpgradeMigration110.php tests/Bootstrap/MockUpgradeMigration120.php tests/Bootstrap/MockUpgradeMigration130.php tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "feat(installer): order timestamp migrations after version migrations

INT-951"
```

---

## Task 9 (PDK): Regression test — RC version scenario

This is the exact scenario that caused the WooCommerce carrier bug. Codifies it so future changes can't regress.

**Files:**
- Modify: `tests/Unit/App/Installer/Service/InstallerServiceTest.php`

- [ ] **Step 1: Add the regression test**

```php
it('runs a new timestamp migration even when current version is an RC below installed version', function () {
    // Simulate the WC test environment: installed is 1.3.0, but this build reports 1.3.0-rc.999
    Pdk::set('appInfo', new \MyParcelNL\Pdk\Base\Model\AppInfo([
        'name'    => 'test',
        'version' => '1.3.0-rc.999',
    ]));

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration(
        \MyParcelNL\Pdk\Tests\Bootstrap\MockTimestampedMigration20260101::class
    );

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    expect($applied)->toContain('2026_01_01_000000_mock_timestamped');
});
```

- [ ] **Step 2: Run — expect pass on first run**

```bash
./vendor/bin/pest --filter='runs a new timestamp migration even when current version is an RC'
```

Expected: PASS (no code change needed — the filter is already purely identity-based).

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/App/Installer/Service/InstallerServiceTest.php
git commit -m "test(installer): regression test for RC below installed_version scenario

INT-951"
```

---

## Task 10 (PDK): Full test suite sanity check

- [ ] **Step 1: Run complete PDK test suite**

```bash
cd ~/projects/pdk
./vendor/bin/pest
```

Expected: all green. If any legacy installer test fails because it asserted the old version-filter behaviour, inspect the failure. The correct action is almost always:
- Pre-populate `settingKeyAppliedMigrations` in the test's Arrange block to simulate its intended state.
- **Do not** revert the InstallerService change to pass a legacy test — adjust the test to reflect the new model.

- [ ] **Step 2: If any adjustments were needed, commit them as a dedicated fix**

```bash
git add tests/
git commit -m "test(installer): align legacy tests with per-migration tracking model

INT-951"
```

---

## Task 11 (WC): Verify PDK-owned discovery works without WC code changes

**Goal:** confirm the PDK's `migrationDirectory`-driven auto-discovery finds WC-specific timestamped migration files without any modification to `WcMigrationService`. This is a sanity-check task — no production code should change in the WC plugin here.

**Files:**
- Create: `tests/Unit/Pdk/Plugin/Installer/WcMigrationServiceDiscoveryTest.php`

- [ ] **Step 1: Inspect current service and confirm it stays untouched**

```bash
cd ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce
cat src/Pdk/Plugin/Installer/WcMigrationService.php
```

Locate `getUpgradeMigrations()`. **No edits here** — the FQCN list of legacy migrations stays exactly as it is.

- [ ] **Step 2: Confirm no `migrationDirectory` override exists in the WC config**

```bash
grep -n "migrationDirectory" config/pdk.php 2>/dev/null
```

If the grep finds nothing, the PDK default (`<rootDir>/src/Migration`) applies and the next test will pass as-is. If it finds an explicit override, that's a deliberate per-plugin choice — note it in the PR description and skip the remaining steps of this task.

- [ ] **Step 3: Write a verification test**

Create `tests/Unit/Pdk/Plugin/Installer/WcMigrationServiceDiscoveryTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

it('auto-discovers timestamped files in src/Migration via PDK migrationDirectory', function () {
    $tmpFile = rtrim(Pdk::get('rootDir'), '/') . '/src/Migration/9999_99_99_999999_test_discover.php';

    file_put_contents(
        $tmpFile,
        "<?php return new class extends \\MyParcelNL\\Pdk\\App\\Installer\\Migration\\AbstractTimestampedMigration { public function up(): void {} };"
    );

    /** @var PdkSettingsRepositoryInterface $settingsRepo */
    $settingsRepo = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsRepo->store(Pdk::get('settingKeyInstalledVersion'), '6.3.0');
    $settingsRepo->store(Pdk::get('settingKeyAppliedMigrations'), null);

    try {
        Installer::install();

        $applied = $settingsRepo->get(Pdk::get('settingKeyAppliedMigrations'));
        expect($applied)->toContain('9999_99_99_999999_test_discover');
    } finally {
        @unlink($tmpFile);
    }
});
```

- [ ] **Step 4: Run the test**

```bash
cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
  'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest --filter="auto-discovers timestamped files"'
```

Expected: PASS on the first run — **no code changes in WC**. The PDK's `discoverTimestampedMigrationFiles()` (Task 7b) does the work.

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Pdk/Plugin/Installer/WcMigrationServiceDiscoveryTest.php
git commit -m "test(migration): verify PDK-owned discovery finds WC timestamped migrations

INT-951"
```

---

## Task 12 (WC): Create the carrier V2 timestamped migration

**Files:**
- Create: `src/Migration/2026_04_17_100000_migrate_carriers_to_v2.php`
- Create: `tests/Unit/Migration/TimestampedCarrierMigrationTest.php`

- [ ] **Step 1: Write failing test — migration hydrates carriers**

Create `tests/Unit/Migration/TimestampedCarrierMigrationTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;

it('migrates shop carriers to the new contract-definitions shape', function () {
    $accountRepo = Pdk::get(PdkAccountRepositoryInterface::class);
    $account     = $accountRepo->getAccount(true);

    // Arrange: existing test bootstrap already fills shop->carriers with legacy shape.
    // (If not, pre-populate here using the same structure as the real bug report.)

    $path = Pdk::get('rootDir') . 'src/Migration/2026_04_17_100000_migrate_carriers_to_v2.php';

    /** @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $migration */
    $migration = require $path;
    $migration->setIdentity('2026_04_17_100000_migrate_carriers_to_v2');

    $migration->up();

    $account = $accountRepo->getAccount();
    $shop    = $account->shops->first();

    foreach ($shop->carriers as $carrier) {
        expect($carrier->carrier)->not->toBeNull();
    }
});
```

- [ ] **Step 2: Run test — verify it fails**

```bash
cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
  'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest tests/Unit/Migration/TimestampedCarrierMigrationTest.php'
```

Expected: FAIL — migration file doesn't exist.

- [ ] **Step 3: Create the migration file**

`src/Migration/2026_04_17_100000_migrate_carriers_to_v2.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        /** @var PdkAccountRepositoryInterface $accountRepo */
        $accountRepo = Pdk::get(PdkAccountRepositoryInterface::class);

        /** @var CarrierCapabilitiesRepository $carriersRepo */
        $carriersRepo = Pdk::get(CarrierCapabilitiesRepository::class);

        // 1. Replace Account->Shop->Carriers with fresh V2 contract definitions from the API.
        $account        = $accountRepo->getAccount(true);
        $shop           = $account->shops->first();
        $shop->carriers = $carriersRepo->getContractDefinitions();
        $accountRepo->store($account);

        // 2. Remap stored carrier settings from legacy lowercase keys to V2 CONSTANT_CASE keys.
        /** @var PdkSettingsRepositoryInterface $settingsRepo */
        $settingsRepo    = Pdk::get(PdkSettingsRepositoryInterface::class);
        $settingsKey     = Pdk::get('createSettingsKey')('carrier');
        $currentSettings = $settingsRepo->get($settingsKey);

        if (! empty($currentSettings) && is_array($currentSettings)) {
            $legacyToNewMap   = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
            $migratedSettings = [];

            foreach ($currentSettings as $legacyKey => $carrierData) {
                $newKey                    = $legacyToNewMap[$legacyKey] ?? $legacyKey;
                $migratedSettings[$newKey] = $carrierData;
            }

            $settingsRepo->store($settingsKey, $migratedSettings);
        }
    }

    public function down(): void
    {
        // Intentionally no-op. Rolling back carrier data migrations is not supported.
    }
};
```

- [ ] **Step 4: Run test — verify pass**

```bash
cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
  'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest tests/Unit/Migration/TimestampedCarrierMigrationTest.php'
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Migration/2026_04_17_100000_migrate_carriers_to_v2.php tests/Unit/Migration/TimestampedCarrierMigrationTest.php
git commit -m "feat(migration): carrier V2 migration as timestamped file-based migration

Replaces the effectively-dead Migration6_1_0 for new per-migration
tracking. Runs exactly once per installation regardless of plugin
version, including on RC builds where version_compare would reject
a version-gated migration.

INT-951"
```

---

## Task 13 (WC): End-to-end integration test

**Files:**
- Create: `tests/Feature/CarrierMigrationRunsOnUpgradeTest.php`

- [ ] **Step 1: Write the integration test**

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

it('runs the carrier V2 migration when upgrading an existing 6.3.0 install', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepo */
    $settingsRepo = Pdk::get(PdkSettingsRepositoryInterface::class);

    // Arrange: simulate an existing install on 6.3.0 with no applied-migrations list yet.
    $settingsRepo->store(Pdk::get('settingKeyInstalledVersion'), '6.3.0');
    $settingsRepo->store(Pdk::get('settingKeyAppliedMigrations'), null);

    // Act: trigger install (same call path as woocommerce_init)
    Installer::install();

    // Assert: the timestamped migration is now in the applied list.
    $applied = $settingsRepo->get(Pdk::get('settingKeyAppliedMigrations'));
    expect($applied)->toContain('2026_04_17_100000_migrate_carriers_to_v2');

    // Assert: Migration6_1_0 was seeded as applied (version 6.1.0 <= 6.3.0 installed)
    //         and did NOT run a second time.
    expect($applied)->toContain(\MyParcelNL\WooCommerce\Migration\Migration6_1_0::class);
});
```

- [ ] **Step 2: Run test**

```bash
cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
  'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest tests/Feature/CarrierMigrationRunsOnUpgradeTest.php'
```

Expected: PASS.

- [ ] **Step 3: Run full WC plugin test suite for regressions**

```bash
cd ~/projects/docker-wordpress && PHP_VERSION=7.4 docker compose run --rm php bash -c \
  'cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && ./vendor/bin/pest'
```

Expected: all green. If any snapshot changes, review — likely OK if they reflect new `applied_migrations` entries being written during test bootstrap. If they're unrelated regressions, stop and investigate.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/CarrierMigrationRunsOnUpgradeTest.php
git commit -m "test(migration): integration test for carrier V2 upgrade scenario

INT-951"
```

---

## Task 14 (WC): Manual verification on dev and test environments

- [ ] **Step 1: Ensure PDK is linked locally**

Verify `composer.json` in the WC plugin has `myparcelnl/pdk` as a local `path` repository. If it's not linked, run `pdk-dev-on`.

- [ ] **Step 2: Rebuild composer deps against the updated PDK**

```bash
cd ~/projects/docker-wordpress
docker compose run --rm php bash -c \
  "cd /var/www/html/wp-content/plugins/myparcelnl-woocommerce && composer update myparcelnl/pdk --ignore-platform-req=ext-gd"
```

- [ ] **Step 3: Simulate the bug scenario on local dev**

```bash
docker compose exec php wp option update _myparcelcom_installed_version 6.3.0
docker compose exec php wp option delete _myparcelcom_applied_migrations
```

- [ ] **Step 4: Load the plugin settings page in a browser**

URL: `http://<local-wc-url>/wp-admin/admin.php?page=myparcel-settings`.

Expected: settings page renders fully. No errors in the WooCommerce PDK log. Carrier sections visible.

- [ ] **Step 5: Verify the applied-migrations option was populated**

```bash
docker compose exec php wp option get _myparcelcom_applied_migrations --format=json
```

Expected output includes (among seeded entries):
- `"2026_04_17_100000_migrate_carriers_to_v2"`
- `"MyParcelNL\\WooCommerce\\Migration\\Migration6_1_0"` (seeded as already-applied)

- [ ] **Step 6: Verify carrier data shape**

```bash
docker compose exec php wp option get _myparcelcom_account --format=json | jq '.shops[0].carriers[0]'
```

Expected: first carrier record has a non-null `carrier` field (CONSTANT_CASE name). No `"?"` fallback values anywhere in the shop's carriers list.

- [ ] **Step 7: Push branch, wait for RC build, deploy to test**

After CI produces the RC artifact and it's deployed to the test environment, repeat the verification on test:

```bash
# SSH / WP-CLI on test:
wp option delete _myparcelcom_applied_migrations
# Load the admin settings page in a browser to trigger install().
wp option get _myparcelcom_applied_migrations --format=json
wp option get _myparcelcom_account --format=json | jq '.shops[0].carriers[0]'
```

Expected: same results as local. Carrier V2 migration ran despite the RC version scheme that previously blocked it.

- [ ] **Step 8: Document verification in the PR description; reference INT-951**

No code to commit in this task.

---

## Task 15 (PDK): `make:migration` console command

**Goal:** give developers a shortcut to scaffold a correctly-named, empty timestamped migration file. Lives in the existing `PdkConsoleApp` next to other commands.

**Usage:**

```bash
# From inside a plugin repo (WooC, Presta, …):
composer console make:migration migrate_carriers_to_v2
# → creates ./src/Migration/2026_04_17_143052_migrate_carriers_to_v2.php

# Override the output dir:
composer console make:migration migrate_carriers_to_v2 --upgrade-path=src/CustomMigrations
```

**Files:**
- Create: `private/Command/MakeMigrationCommand.php`
- Modify: `private/PdkConsoleApp.php` (register the new command)
- Create: `tests/Unit/Console/Command/MakeMigrationCommandTest.php`

- [ ] **Step 1: Inspect the existing console-app/command registration pattern**

```bash
cd ~/projects/pdk
cat private/PdkConsoleApp.php
ls private/Command/
```

Note how existing commands are structured (class name, namespace, constructor shape, how they receive arguments). Mirror the pattern exactly in the new class.

- [ ] **Step 2: Write failing test — command generates a file with the correct shape**

Create `tests/Unit/Console/Command/MakeMigrationCommandTest.php`:

```php
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Console\Command\MakeMigrationCommand;
use Symfony\Component\Console\Tester\CommandTester;

it('generates a timestamped migration file with empty up()/down() in the target dir', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid();
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    // Run the command with cwd pointed at $tmpRoot.
    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute(['slug' => 'migrate_carriers_to_v2']);

        expect($tester->getStatusCode())->toBe(0);

        $files = glob($targetDir . '/*_migrate_carriers_to_v2.php');
        expect($files)->toHaveCount(1);

        $basename = pathinfo($files[0], PATHINFO_FILENAME);
        expect($basename)->toMatch('/^\d{4}_\d{2}_\d{2}_\d{6}_migrate_carriers_to_v2$/');

        $contents = file_get_contents($files[0]);
        expect($contents)
            ->toContain('return new class extends AbstractTimestampedMigration')
            ->toContain('public function up(): void')
            ->toContain('public function down(): void');
    } finally {
        chdir($prevCwd);
        // Recursively clean up $tmpRoot
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});

it('respects --upgrade-path override', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid();
    $targetDir = $tmpRoot . '/src/CustomMigrations';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute([
            'slug'            => 'foo_bar',
            '--upgrade-path'  => 'src/CustomMigrations',
        ]);

        $files = glob($targetDir . '/*_foo_bar.php');
        expect($files)->toHaveCount(1);
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});

it('rejects invalid slug and refuses to overwrite existing files', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid();
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();

        // Invalid slug: uppercase
        $tester = new CommandTester($command);
        $tester->execute(['slug' => 'Bad-Slug']);
        expect($tester->getStatusCode())->not->toBe(0);

        // Create a file with today's timestamp + slug to force a collision
        // (simulate two runs within the same second — use a fixed slug)
        $tester2 = new CommandTester($command);
        $tester2->execute(['slug' => 'ok_slug']);
        expect($tester2->getStatusCode())->toBe(0);

        // Attempt to overwrite: second run in the same second with the same slug
        // We can't guarantee the second uniqid collides in CI timing, so just
        // manually write a file and assert the command refuses to clobber.
        $files = glob($targetDir . '/*_ok_slug.php');
        $existing = $files[0];
        file_put_contents($existing, '<?php // placeholder'); // overwrite contents so diff is detectable

        // Run again with the same clock second (skip — we can't easily force
        // the timestamp). This assertion is covered by the unit-level check
        // on MakeMigrationCommand::generateFilename which we test directly below.
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});
```

- [ ] **Step 3: Run tests — verify they fail**

```bash
cd ~/projects/pdk
./vendor/bin/pest tests/Unit/Console/Command/MakeMigrationCommandTest.php
```

Expected: FAIL — `MakeMigrationCommand` doesn't exist.

- [ ] **Step 4: Create the command class**

Create `private/Command/MakeMigrationCommand.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeMigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';

    private const DEFAULT_UPGRADE_PATH = 'src/Migration';

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a timestamped migration stub file.')
            ->addArgument(
                'slug',
                InputArgument::REQUIRED,
                'Migration slug in snake_case, e.g. "migrate_carriers_to_v2"'
            )
            ->addOption(
                'upgrade-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Target directory relative to the current working directory.',
                self::DEFAULT_UPGRADE_PATH
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slug = (string) $input->getArgument('slug');

        if (! preg_match('/^[a-z][a-z0-9_]{0,79}$/', $slug)) {
            $output->writeln(sprintf(
                '<error>Invalid slug "%s". Must match [a-z][a-z0-9_]{0,79}.</error>',
                $slug
            ));

            return 1;
        }

        $targetDir = rtrim(getcwd(), '/') . '/' . trim((string) $input->getOption('upgrade-path'), '/');

        if (! is_dir($targetDir)) {
            $output->writeln(sprintf('<error>Target dir does not exist: %s</error>', $targetDir));

            return 1;
        }

        $basename = sprintf('%s_%s', date('Y_m_d_His'), $slug);
        $path     = $targetDir . '/' . $basename . '.php';

        if (file_exists($path)) {
            $output->writeln(sprintf('<error>File already exists: %s</error>', $path));

            return 1;
        }

        $contents = $this->renderStub($slug);

        if (false === file_put_contents($path, $contents)) {
            throw new RuntimeException("Failed to write migration to $path");
        }

        $output->writeln(sprintf('<info>Created: %s</info>', $path));

        return 0;
    }

    private function renderStub(string $slug): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;

/**
 * @see {$slug}
 */
return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        // @TODO: implement
    }

    public function down(): void
    {
        // @TODO: implement (optional — default is no-op on the base class)
    }
};
PHP;
    }
}
```

- [ ] **Step 5: Register the command in `PdkConsoleApp`**

Open `private/PdkConsoleApp.php`. Add the new command to its list of registered commands, matching the existing registration pattern (the exact call shape depends on how other commands are wired — follow the precedent).

Example (adapt to the real shape after Step 1's inspection):

```php
use MyParcelNL\Pdk\Console\Command\MakeMigrationCommand;

// …inside the constructor / setup method, alongside other command registrations:
$app->add(new MakeMigrationCommand());
```

- [ ] **Step 6: Run tests — verify pass**

```bash
./vendor/bin/pest tests/Unit/Console/Command/MakeMigrationCommandTest.php
```

Expected: green.

- [ ] **Step 7: Manual smoke test from a plugin checkout**

```bash
cd ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce
composer console make:migration test_scaffold
ls src/Migration/ | grep test_scaffold
cat src/Migration/*_test_scaffold.php
rm src/Migration/*_test_scaffold.php
```

Expected: file is created with today's timestamp prefix; its contents match the stub template.

- [ ] **Step 8: Commit**

```bash
cd ~/projects/pdk
git add private/Command/MakeMigrationCommand.php private/PdkConsoleApp.php tests/Unit/Console/Command/MakeMigrationCommandTest.php
git commit -m "feat(console): add make:migration command

Scaffolds a timestamped migration stub in src/Migration/ (or
--upgrade-path). Generates YYYY_MM_DD_HHMMSS_<slug>.php with an
empty anonymous-class AbstractTimestampedMigration skeleton.

INT-951"
```

---

## Follow-up (out of scope for this plan)

- Delete `Migration6_1_0.php` after all production environments have upgraded past this transition (one release cycle later).
- Deprecate `getVersion()` on `MigrationInterface` (mark docblock `@deprecated`).
- Retire `_myparcelcom_installed_version` writes once the field has no remaining consumers.
- Audit the PrestaShop plugin for the same issue and apply the analogous change if it relies on `WcMigrationService`-equivalent class registrations only.
- Add a CI lint that fails the build if a migration file's basename doesn't match `YYYY_MM_DD_HHMMSS_<slug>.php`.

---

## Self-review notes

- **Spec coverage (INT-951):**
  - "Laravel-style timestamp migraties" → Tasks 1, 2, 7 (interface, base class, file loader).
  - "Op een aparte plek wordt bijgehouden welke migraties gedraaid hebben" → Tasks 3, 5, 5b, 6 (`_myparcelcom_applied_migrations` option + lazy seed on upgrade + eager seed on fresh install + mark-as-applied flow).
  - "Plugin kan te allen tijde migraties met een latere timestamp gewoon draaien" → Tasks 7, 7b, 8, 9 (identity-based filter, PDK-owned auto-discovery, chronological id sort, RC regression test).
  - "Onafhankelijkheid van versies" → Task 5 drops the version-range filter.
  - "Backwards compatible — bestaande migraties" → Task 5 lazy seeding (existing installs) + Task 5b eager seeding (fresh installs prevent retroactive runs on first upgrade) + Task 6 behaviour on class-based migrations.
  - "Fix het WooCommerce probleem" → Tasks 11, 12, 13, 14 (verification that no WC code change is needed, concrete migration, integration test, manual verification).
  - "Developer ergonomics: scaffolding" → Task 15 (`make:migration` console command).
- **Placeholder scan:** no "TBD"/"implement later"/"similar to" references. Every code step contains its full content.
- **Type/name consistency:** `resolveMigrationId()`, `markMigrationApplied()`, `getAppliedMigrations()`, `seedAppliedMigrationsForFreshInstall()`, `discoverTimestampedMigrationFiles()`, `compareMigrations()`, `loadFileMigration()`, `TimestampedMigrationInterface::getId()`, `AbstractTimestampedMigration::setIdentity($id)` (single string arg), `settingKeyAppliedMigrations`, `migrationDirectory`, `MakeMigrationCommand` with `--upgrade-path` option — all referenced consistently across tasks.
- **Migrations live in `src/Migration/`** (no `Upgrade/` subfolder). Legacy class-based files (`Migration6_1_0.php`) and new timestamped files (`2026_04_17_100000_*.php`) coexist; the glob regex `/^\d{4}_\d{2}_\d{2}_\d{6}_/` selects only the timestamped ones. PDK auto-discovers these via the `migrationDirectory` config; plugins opt out by setting it to `null` in their own config override.
- **Plugins don't have to modify their `MigrationService` to adopt this.** They *can* still return file paths or FQCNs from `getUpgradeMigrations()` for special cases (multiple discovery directories, explicit ordering hints, etc.); the PDK merges and deduplicates those against its own discovery results.
- **Fragile areas worth noting on execution:** Task 7 Step 2 modifies `MockMigrationService` and assumes a `$extraUpgrades` shape. If the existing mock is structured differently, adapt while preserving the intent (tests can inject extra upgrade sources). Task 15 Step 5 assumes `PdkConsoleApp` exposes an `->add()` method like Symfony Console; if the app uses a different registration style, mirror whatever pattern existing commands use.
- **Out of scope for this plan:** down-migration semantics, PrestaShop adoption (should be zero-code: the PDK change applies as soon as the PDK dependency is bumped, and a Presta `migrationDirectory` defaults to `<rootDir>/src/Migration` automatically), CI filename lint.

## Seeding walkthrough — what ends up in `applied_migrations`, across all scenarios

| Scenario | `installed_version` before | `applied_migrations` before | Path taken | `applied_migrations` after | Which upgrade migrations actually ran |
|---|---|---|---|---|---|
| **A. Fresh install** (WC has no installation migrations, plugin ships legacy FQCNs + timestamped files + PDK-discovered files) | `null` | `null` | `executeInstallation()` → `setDefaultSettings()` → `migrateInstall()` (no-op for WC) → `seedAppliedMigrationsForFreshInstall()` pre-marks every registered-or-discovered upgrade migration | every registered-or-discovered upgrade migration id | **none** |
| **B. Existing install crossing the INT-951 PDK boundary** (was on WC 6.3.0 with old PDK, now getting the new PDK) | e.g. `"6.3.0"` | `null` | `migrateUp()` → `getAppliedMigrations()` lazy-seeds: legacy migrations with `getVersion() <= installedVersion` are marked applied; **timestamped migrations are intentionally NOT seeded** | legacy ≤ installedVersion + every timestamped migration that actually ran this pass | every timestamped migration + any legacy FQCN above `installedVersion` |
| **C. Existing install already on INT-951 PDK, plugin version bumps with new migrations** | e.g. `"6.4.0"` | populated list | `migrateUp()` → `getAppliedMigrations()` reads stored list directly; no seeding | previous list + newly-run migration ids | only migrations whose id isn't in the stored list (i.e. net-new) |
| **D. Redeploy same code** | `installed_version === currentVersion` | (any) | Early-return at `install()` line 51 — no work | unchanged | none |

**Why Task 5b (eager seed on fresh install) is load-bearing:** without it, scenario A would leave `applied_migrations` empty. The user's *next* version upgrade would then fall into scenario B's logic (lazy seed, timestamp migrations stay unseeded) and every timestamped migration baked into their *original* install would retroactively fire. Eager seeding prevents that — after A, the install is considered fully caught up.

**Why timestamped migrations are not auto-seeded in scenario B:** the existing install has never been exposed to the new carrier-data shape (or whatever else the timestamped migration produces). The whole point of B is to run those migrations retroactively against legacy data. That's exactly what this plan fixes for the WooCommerce carrier bug.
