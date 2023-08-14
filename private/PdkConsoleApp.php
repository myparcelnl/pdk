<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console;

use MyParcelNL\Pdk\Console\Command\GenerateDocumentationCommand;
use MyParcelNL\Pdk\Console\Command\GeneratePhpHelperCommand;
use MyParcelNL\Pdk\Console\Command\GenerateTypeScriptTypesCommand;
use Symfony\Component\Console\Application;

/**
 * @see ../bin/console
 */
final class PdkConsoleApp
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $app = new Application();

        $app->add(new GenerateDocumentationCommand());
        $app->add(new GeneratePhpHelperCommand());
        $app->add(new GenerateTypeScriptTypesCommand());

        $app->run();
    }
}
