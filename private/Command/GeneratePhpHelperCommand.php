<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\Php\PhpHelperGenerator;

final class GeneratePhpHelperCommand extends AbstractGenerateTypesCommand
{
    protected static $defaultName = 'generate:php';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Generate PHP helper');
    }

    /**
     * @return string
     */
    protected function getGeneratorClass(): string
    {
        return PhpHelperGenerator::class;
    }

    /**
     * @return string[]
     */
    protected function getSourceDirectories(): array
    {
        return array_merge(
            parent::getSourceDirectories(),
            [
                __DIR__ . '/../../private',
            ]
        );
    }
}
