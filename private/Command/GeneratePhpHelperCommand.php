<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\Php\PhpHelperGenerator;

final class GeneratePhpHelperCommand extends AbstractGenerateTypesCommand
{
    protected static $defaultName = 'generate:php';

    protected function configure(): void
    {
        $this->setDescription('Generate PHP helper');
    }

    /**
     * @return string[]
     */
    protected function getDirectories(): array
    {
        return array_merge(
            parent::getDirectories(),
            [
                'private',
            ]
        );
    }

    /**
     * @return string
     */
    protected function getGeneratorClass(): string
    {
        return PhpHelperGenerator::class;
    }
}
