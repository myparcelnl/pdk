<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\Facade\Logger;

final class MockInstallerService extends InstallerService
{
    /**
     * @param ...$args
     *
     * @return void
     */
    protected function executeInstallation(...$args): void
    {
        parent::executeInstallation($args);

        Logger::debug('install arguments', $args);
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    protected function executeUninstallation(...$args): void
    {
        parent::executeUninstallation($args);

        Logger::debug('uninstall arguments', $args);
    }
}
