<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Installer\Contract;

interface InstallerServiceInterface
{
    /**
     * Install the app, or upgrade it if it is already installed.
     */
    public function install(): void;

    /**
     * Uninstall the app.
     */
    public function uninstall(): void;
}
