<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\Facade\Logger;

final class MockInstallerService extends InstallerService
{
    /**
     * @param ...$args
     */
    protected function executeInstallation(...$args): void
    {
        parent::executeInstallation($args);

        Logger::debug('install arguments', $args);
    }

    /**
     * @param ...$args
     */
    protected function executeUninstallation(...$args): void
    {
        parent::executeUninstallation($args);

        Logger::debug('uninstall arguments', $args);
    }
}
