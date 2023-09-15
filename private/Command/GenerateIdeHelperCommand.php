<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\Php\IdeHelperGenerator;

final class GenerateIdeHelperCommand extends AbstractGenerateTypesCommand
{
    protected static $defaultName = 'generate:ide-helper';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Generate IDE helper file');
    }

    protected function getDefaultSourceDirectories(): array
    {
        return parent::getDefaultSourceDirectories() + ['private'];
    }

    protected function getGeneratorClass(): string
    {
        return IdeHelperGenerator::class;
    }
}
