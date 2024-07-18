<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console;

use MyParcelNL\Pdk\Console\Command\GenerateDocumentationCommand;
use MyParcelNL\Pdk\Console\Command\GenerateFactoryCommand;
use MyParcelNL\Pdk\Console\Command\GenerateIdeHelperCommand;
use MyParcelNL\Pdk\Console\Command\GenerateTypeScriptTypesCommand;
use MyParcelNL\Pdk\Console\Command\ParseSourceCommand;
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

        $app->addCommands([
            new ParseSourceCommand(),
            new GenerateDocumentationCommand(),
            new GenerateFactoryCommand(),
            new GenerateIdeHelperCommand(),
            new GenerateTypeScriptTypesCommand(),
        ]);

        $app->run();
    }
}
