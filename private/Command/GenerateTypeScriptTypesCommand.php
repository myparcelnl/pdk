<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\TypeScript\TypescriptHelperGenerator;

final class GenerateTypeScriptTypesCommand extends AbstractGenerateTypesCommand
{
    protected static $defaultName = 'generate:typescript';

    protected function configure(): void
    {
        $this->setDescription('Generate TypeScript types');
    }

    /**
     * @return string
     */
    protected function getGeneratorClass(): string
    {
        return TypescriptHelperGenerator::class;
    }
}

