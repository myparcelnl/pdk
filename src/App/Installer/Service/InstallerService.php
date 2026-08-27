<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

class InstallerService implements InstallerServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface
     */
    private $migrationService;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface $migrationService
     */
    public function __construct(
        PdkSettingsRepositoryInterface $settingsRepository,
        MigrationServiceInterface      $migrationService
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->migrationService   = $migrationService;
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    public function install(...$args): void
    {
        $installedVersion = $this->getInstalledVersion();
        $currentVersion   = Pdk::getAppInfo()->version;

        // A pending migration is reason enough to run, whatever the version says. Gating on the
        // version alone skips a migration that ships without a bump, and permanently skips one
        // that reported failure, because the version is written even when a migration fails.
        if ($installedVersion === $currentVersion && ! $this->hasPendingMigrations()) {
            return;
        }

        if (! $this->claimMigrationRun()) {
            Logger::debug('Another run is migrating. Skipping this pass.');

            return;
        }

        try {
            Pdk::clearCache();

            if ($installedVersion) {
                Logger::debug("Migrating from $installedVersion to $currentVersion");
                $this->migrateUp($currentVersion);
            } else {
                Logger::debug("Installing $currentVersion");
                $this->executeInstallation(...$args);
            }

            $this->updateInstalledVersion($currentVersion);
        } finally {
            $this->releaseMigrationRun();
        }
    }

    /**
     * Reports whether any registered or auto-discovered upgrade migration is not yet recorded
     * as applied. Plugins that decide for themselves when to run an upgrade should ask this
     * instead of comparing version strings.
     *
     * @return bool
     */
    public function hasPendingMigrations(): bool
    {
        $collection = $this->getUpgradeMigrations();

        if ($collection->isEmpty()) {
            return false;
        }

        $applied = $this->getAppliedMigrations();

        // An empty list on an install that predates per-migration tracking means "not seeded
        // yet", not "nothing to do". Seeding writes to the settings, so it waits for the
        // claimed run in migrateUp().
        if (empty($applied)) {
            return true;
        }

        return $this->filterUnapplied($collection, $applied)
            ->isNotEmpty();
    }

    /**
     * Claims the migration run by storing the current time, and reports whether this run got
     * it. A claim older than the timeout is treated as abandoned: a run killed mid-migration
     * cannot clear its own claim, and without this every later migration would be blocked for
     * good.
     *
     * @return bool
     */
    protected function claimMigrationRun(): bool
    {
        $key     = Pdk::get('settingKeyMigrationLock');
        $timeout = (int) Pdk::get('migrationLockTimeout');

        // Known gap: two runs that both read the claim before either stores it will both
        // migrate. Closing it needs a write that fails when a value is already there, which
        // the settings repository cannot do and every platform would have to build itself.
        // Accepted because the gap is one database round trip wide, is only open in the
        // minutes after an update, and before this there was no claim at all.
        $claimedAt = (int) $this->settingsRepository->get($key);

        if (time() - $claimedAt < $timeout) {
            return false;
        }

        $this->settingsRepository->store($key, time());

        return true;
    }

    /**
     * @return void
     */
    protected function releaseMigrationRun(): void
    {
        $this->settingsRepository->store(Pdk::get('settingKeyMigrationLock'), 0);
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    public function uninstall(...$args): void
    {
        Pdk::clearCache();

        $installedVersion = $this->getInstalledVersion();

        if ($installedVersion) {
            Logger::debug("Uninstalling $installedVersion");
            $this->executeUninstallation(...$args);
            $this->updateInstalledVersion(null);
        }
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    protected function executeInstallation(...$args): void
    {
        $this->setDefaultSettings();
        $this->migrateInstall();
        // migrateInstall() records the installation migrations as applied; this call then
        // overwrites applied_migrations with the upgrade-migration ids. That overwrite is
        // intentional: installation migrations never appear in the upgrade set, so dropping
        // their ids here cannot cause them to re-run.
        $this->markAllUpgradeMigrationsApplied();
    }

    /**
     * Records every registered and auto-discovered upgrade migration as applied, without
     * running them. Used on a fresh install: the new installation already reflects their
     * end state, so marking them applied stops the user's first later upgrade from
     * replaying migrations that are effectively "baked into" this version.
     *
     * @return void
     */
    protected function markAllUpgradeMigrationsApplied(): void
    {
        $registered = method_exists($this->migrationService, 'getUpgradeMigrations')
            ? $this->migrationService->getUpgradeMigrations()
            : $this->migrationService->all();

        $ids = $this->createMigrationCollection($this->mergeMigrationSources($registered))
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store(Pdk::get('settingKeyAppliedMigrations'), $ids);
    }

    /**
     * @param  array $args
     *
     * @return void
     */
    protected function executeUninstallation(...$args): void
    {
        $this->migrateUninstall();
    }

    /**
     * @return null|string
     */
    protected function getInstalledVersion(): ?string
    {
        return $this->settingsRepository->get(Pdk::get('settingKeyInstalledVersion'));
    }

    /**
     * Returns the identity string used to track whether a migration has been applied.
     * Falls back to the class FQCN for class-based migrations that don't implement
     * TimestampedMigrationInterface.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $migration
     *
     * @return string
     */
    protected function resolveMigrationId(MigrationInterface $migration): string
    {
        if ($migration instanceof TimestampedMigrationInterface) {
            return $migration->getId();
        }

        return get_class($migration);
    }

    /**
     * Returns the migration identities recorded as applied on this installation.
     * A migration runs exactly once — when its identity is not yet in this list.
     *
     * @return string[]
     */
    protected function getAppliedMigrations(): array
    {
        $stored = $this->settingsRepository->get(Pdk::get('settingKeyAppliedMigrations'));

        return is_array($stored) ? $stored : [];
    }

    /**
     * Seeds the applied list the first time per-migration tracking runs against an install
     * that predates it — the upgrade in which this tracking is introduced, when
     * applied_migrations does not exist yet but installed_version does. Versioned migrations
     * at or below the installed version are recorded as applied so they are not re-run;
     * timestamp-based migrations are intentionally left out so they do run.
     *
     * No-op once the list is populated (including after a fresh install, where
     * markAllUpgradeMigrationsApplied() has already seeded it).
     *
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $allMigrations
     *
     * @return void
     */
    protected function seedAppliedMigrationsFromInstalledVersion(Collection $allMigrations): void
    {
        $key    = Pdk::get('settingKeyAppliedMigrations');
        $stored = $this->settingsRepository->get($key);

        // The repository returns an empty array for a never-stored key, indistinguishable
        // from a deliberately-empty list; treat empty as "not yet seeded". Re-seeding is
        // idempotent, so this is safe.
        if (is_array($stored) && ! empty($stored)) {
            return;
        }

        $installedVersion = $this->getInstalledVersion();

        if (! $installedVersion) {
            // Defensive: both callers (migrateUp on upgrade, and migrateDown on uninstall)
            // only run when installed_version is set, so this is never reached in practice.
            // A fresh install seeds via markAllUpgradeMigrationsApplied().
            return; // @codeCoverageIgnore
        }

        $seed = $allMigrations
            ->filter(function (MigrationInterface $m) use ($installedVersion) {
                return ! $m instanceof TimestampedMigrationInterface
                    && version_compare($m->getVersion(), $installedVersion, '<=');
            })
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store($key, $seed);
    }

    /**
     * Appends a migration identity to the persisted applied list.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $migration
     *
     * @return void
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

    /**
     * @return void
     */
    protected function migrateDown(): void
    {
        $collection = $this->getUpgradeMigrations();

        // Reverse exactly the migrations recorded as applied — for both versioned and
        // timestamp-based migrations. Version math is not used here: it would skip an
        // applied versioned migration whose version compares "newer" than an RC
        // installed_version (e.g. 1.3.0 vs 1.3.0-rc.4), the same bug this tracking fixes.
        $this->seedAppliedMigrationsFromInstalledVersion($collection);
        $applied = $this->getAppliedMigrations();

        $this->runDownMigrations(
            $collection->filter(function (MigrationInterface $migration) use ($applied) {
                return in_array($this->resolveMigrationId($migration), $applied, true);
            })
        );
    }

    /**
     * @return void
     */
    protected function migrateInstall(): void
    {
        $this->runUpMigrations($this->getInstallationMigrations());
    }

    /**
     * $version is unused: what runs is decided by the applied list. Keep the parameter,
     * PsInstallerService overrides this method and calls parent::migrateUp($version).
     *
     * @param  string $version
     *
     * @return void
     */
    protected function migrateUp(string $version): void
    {
        $collection = $this->getUpgradeMigrations();

        // Seeding writes to the settings, so it happens here, inside the claimed run, and not
        // in hasPendingMigrations(), which runs on every request before the claim.
        $this->seedAppliedMigrationsFromInstalledVersion($collection);

        $this->runUpMigrations($this->filterUnapplied($collection, $this->getAppliedMigrations()));
    }

    /**
     * @return void
     */
    protected function setDefaultSettings(): void
    {
        $settings = new Settings(SettingsFacade::getDefaults());

        $this->settingsRepository->storeAllSettings($settings);
    }

    /**
     * @param  null|string $version
     *
     * @return void
     */
    protected function updateInstalledVersion(?string $version): void
    {
        $this->settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $version);
    }

    /**
     * Builds a Collection of MigrationInterface instances from an array of sources.
     * Each source is either an absolute path to a .php file that returns an
     * anonymous-class migration, or an FQCN resolved via the DI container.
     *
     * @param  array<int, string> $migrations
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function createMigrationCollection(array $migrations): Collection
    {
        return Collection::make($migrations)
            ->map(function ($source) {
                // File-based migration: absolute path ending in .php
                if (is_string($source) && '.php' === substr($source, -4)) {
                    if (! is_file($source)) {
                        throw new \RuntimeException(sprintf(
                            'Migration file "%s" was provided but does not exist. Expected a path to an existing .php migration file.',
                            $source
                        ));
                    }

                    return $this->loadFileMigration($source);
                }

                // Class-based migration: FQCN resolved via container
                return Pdk::get($source);
            });
    }

    /**
     * Loads an anonymous-class migration from a file whose basename follows
     * the "YYYY_MM_DD_HHMMSS_<slug>.php" convention, injects identity into
     * AbstractTimestampedMigration instances, and returns the migration.
     *
     * Throws a RuntimeException when the file does not return a MigrationInterface
     * instance or when the filename does not match the required convention.
     *
     * @param  string $path Absolute path to the migration file.
     *
     * @return \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface
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

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection<\MyParcelNL\Pdk\App\Installer\Contract\InstallationMigrationInterface>
     * @todo v3.0.0 remove legacy support
     */
    private function getInstallationMigrations(): Collection
    {
        if (! method_exists($this->migrationService, 'getInstallationMigrations')) {
            Logger::deprecated(
                sprintf('Method "%s::all()"', MigrationServiceInterface::class),
                'getUpgradeMigrations and getInstallationMigrations'
            );

            return new Collection();
        }

        return $this->createMigrationCollection($this->migrationService->getInstallationMigrations());
    }

    /**
     * Every registered and auto-discovered upgrade migration, applied or not.
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection<\MyParcelNL\Pdk\App\Installer\Contract\UpgradeMigrationInterface>
     * @todo v3.0.0 remove legacy support
     */
    private function getUpgradeMigrations(): Collection
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

        return $this->createMigrationCollection($this->mergeMigrationSources($registered));
    }

    /**
     * The migrations from $migrations whose identity is not in $applied.
     *
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $migrations
     * @param  string[]                                 $applied
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function filterUnapplied(Collection $migrations, array $applied): Collection
    {
        return $migrations->filter(function (MigrationInterface $migration) use ($applied) {
            return ! in_array($this->resolveMigrationId($migration), $applied, true);
        });
    }

    /**
     * Combines plugin-registered migration sources with the timestamped files
     * auto-discovered in migrationDirectory, removing duplicates. A source can
     * legitimately appear in both — e.g. a plugin registers a file that also lives in
     * migrationDirectory. Dedupe compares the source strings, so a file registered under
     * a different path than discovery returns (e.g. via a symlink) would not be deduped;
     * plugins should rely on discovery alone or register the exact discovered path.
     *
     * @param  array<int, string> $registered
     *
     * @return string[]
     */
    private function mergeMigrationSources(array $registered): array
    {
        return array_values(array_unique(array_merge(
            $registered,
            $this->discoverTimestampedMigrationFiles()
        )));
    }

    /**
     * Returns absolute paths of every file in the configured migrationDirectory
     * whose basename matches the YYYY_MM_DD_HHMMSS_<slug>.php convention.
     *
     * Returns an empty array if the config key is undefined, null, or does not
     * point to an existing directory — lets plugins opt out by setting
     * migrationDirectory to null in their own config.
     *
     * @return string[]
     */
    private function discoverTimestampedMigrationFiles(): array
    {
        $dir = null;
        try {
            $dir = Pdk::get('migrationDirectory');
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            // migrationDirectory is undefined in this container, so discovery is disabled.
            // Not reachable from the PDK's own tests (its config always defines the key);
            // this exists for a consumer that removes it.
            return [];
            // @codeCoverageIgnoreEnd
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

    private function migrateUninstall(): void
    {
        $this->migrateDown();
        $this->runDownMigrations($this->getInstallationMigrations());
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface> $migrations
     *
     * @return void
     */
    private function runDownMigrations(Collection $migrations): void
    {
        $migrations
            ->sort(function (MigrationInterface $a, MigrationInterface $b) {
                // Reverse the up-order: timestamp-based first (they were last), then version-based descending.
                return $this->compareMigrations($b, $a);
            })
            ->each(function (MigrationInterface $migration) {
                $migration->down();
            });
    }

    /**
     * Sort comparator for migrations. Version-based migrations are ordered by
     * semantic version and run before timestamp-based ones. Timestamp-based
     * migrations are ordered lexicographically by their ID (YYYY_MM_DD_HHMMSS_<slug>),
     * which is equivalent to chronological order.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $a
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $b
     *
     * @return int
     */
    private function compareMigrations(MigrationInterface $a, MigrationInterface $b): int
    {
        $aIsTimestamped = $a instanceof TimestampedMigrationInterface;
        $bIsTimestamped = $b instanceof TimestampedMigrationInterface;

        // Version-based migrations run before timestamp-based ones.
        if ($aIsTimestamped !== $bIsTimestamped) {
            return $aIsTimestamped ? 1 : -1;
        }

        if ($a instanceof TimestampedMigrationInterface && $b instanceof TimestampedMigrationInterface) {
            // Both are timestamped: lexicographic order on ID equals chronological order.
            return strcmp($a->getId(), $b->getId());
        }

        return version_compare($a->getVersion(), $b->getVersion());
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface> $migrations
     *
     * @return void
     */
    private function runUpMigrations(Collection $migrations): void
    {
        $ran = [];

        $migrations
            ->sort(function (MigrationInterface $a, MigrationInterface $b) {
                return $this->compareMigrations($a, $b);
            })
            ->each(function (MigrationInterface $migration) use (&$ran) {
                $id = $this->resolveMigrationId($migration);

                // Run each identity at most once per pass. The collection is pre-filtered by
                // applied state, but two entries can still resolve to the same identity (e.g.
                // a file reachable via differing path strings that source dedupe missed).
                if (in_array($id, $ran, true)) {
                    return;
                }

                $migration->up();
                $ran[] = $id;

                // A migration that reports failure is deliberately left unrecorded, so it runs again on
                // the next load. The remaining migrations still run: one that could not finish should not
                // hold up the rest of the upgrade.
                if ($migration instanceof TimestampedMigrationInterface && $migration->hasFailed()) {
                    Logger::warning('Migration did not finish and will be attempted again.', [
                        'migration' => $id,
                    ]);

                    return;
                }

                $this->markMigrationApplied($migration);
            });
    }
}
