<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Service\MigrationService;

class MockMigrationService extends MigrationService
{
    /**
     * Extra upgrade migration class names registered dynamically during tests.
     * Allows individual tests to inject additional migrations without modifying
     * the fixed base set. Reset between tests via resetExtraUpgrades().
     *
     * @var string[]
     */
    private static $extraUpgrades = [];

    /**
     * Appends a migration class name to the dynamic extras list for the current test.
     *
     * @param  string $source
     *
     * @return void
     */
    public static function addUpgradeMigration(string $source): void
    {
        self::$extraUpgrades[] = $source;
    }

    /**
     * Removes a migration class name from the dynamic extras list.
     *
     * @param  string $source
     *
     * @return void
     */
    public static function removeUpgradeMigration(string $source): void
    {
        self::$extraUpgrades = array_values(
            array_filter(self::$extraUpgrades, function (string $entry) use ($source) {
                return $entry !== $source;
            })
        );
    }

    /**
     * Clears all dynamically registered upgrade migrations. Call this in afterEach
     * to prevent extras from leaking into subsequent tests.
     *
     * @return void
     */
    public static function resetExtraUpgrades(): void
    {
        self::$extraUpgrades = [];
    }

    public function getInstallationMigrations(): array
    {
        return [
            MockInstallationMigration100::class,
        ];
    }

    public function getUpgradeMigrations(): array
    {
        return array_merge(
            [
                MockUpgradeMigration110::class,
                MockUpgradeMigration120::class,
                MockUpgradeMigration130::class,
            ],
            self::$extraUpgrades
        );
    }
}
