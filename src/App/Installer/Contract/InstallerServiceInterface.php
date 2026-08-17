<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

/**
 * hasPendingMigrations() is declared here rather than added to the interface, because adding a
 * method would break every existing implementer. Guard calls with method_exists(), the same way
 * MigrationServiceInterface handles its own later additions.
 *
 * @method bool hasPendingMigrations()
 */
interface InstallerServiceInterface
{
    /**
     * Install the app, or up it if it is already installed.
     */
    public function install(...$args): void;

    /**
     * Uninstall the app.
     */
    public function uninstall(...$args): void;
}
