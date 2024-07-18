<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\Documentation\DocumentationGenerator;

final class GenerateDocumentationCommand extends AbstractGenerateTypesCommand
{
    protected static $defaultName = 'generate:docs';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Generate documentation');
    }

    /**
     * @return string
     */
    protected function getGeneratorClass(): string
    {
        return DocumentationGenerator::class;
    }
}
