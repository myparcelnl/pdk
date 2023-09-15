<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

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
