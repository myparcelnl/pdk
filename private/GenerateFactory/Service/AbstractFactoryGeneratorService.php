<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\GenerateFactory\Service;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Concern\HasCommandContext;
use MyParcelNL\Pdk\Console\GenerateFactory\Contract\FactoryServiceInterface;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;

abstract class AbstractFactoryGeneratorService implements FactoryServiceInterface
{
    use HasCommandContext;

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser
     */
    protected $sourceParser;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    protected $typeParser;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface                     $fileSystem
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser $sourceParser
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser   $typeParser
     */
    public function __construct(
        FileSystemInterface $fileSystem,
        PhpSourceParser     $sourceParser,
        PhpTypeParser       $typeParser
    ) {
        $this->fileSystem   = $fileSystem;
        $this->sourceParser = $sourceParser;
        $this->typeParser   = $typeParser;
    }

    /**
     * @return class-string
     */
    abstract protected function getClass(): string;

    /**
     * @return string
     */
    abstract protected function getTemplateFilename(): string;

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return void
     */
    final public function generate(ClassDefinition $definition): void
    {
        $path = $this->generateFilename($definition);

        if (! $this->input->getOption('force') && $this->fileSystem->fileExists($path)) {
            $this->log(
                sprintf(
                    '<info>Skipping <def>%s</def>, output file already exists.</info>',
                    $definition->ref->getName()
                )
            );
            return;
        }

        $this->fileSystem->mkdir($this->fileSystem->dirname($path), true);
        $this->fileSystem->put($path, $this->createContent($definition));

        $this->log(sprintf('<success>Generated %s</success>', $path));
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function createComments(ClassDefinition $definition): Collection
    {
        return new Collection();
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return string
     */
    protected function createContent(ClassDefinition $definition): string
    {
        return strtr($this->getTemplate(), $this->getTemplateReplacers($definition));
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return string
     */
    protected function createNamespace(ClassDefinition $definition): string
    {
        $basename = Utils::classBasename($this->getClass());
        $parts    = explode('\\', str_replace("\\$basename", '', $definition->ref->getNamespaceName()));

        array_splice($parts, 2, 0, ['Tests', 'Factory', $basename]);

        return implode('\\', $parts);
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return array|string|string[]
     */
    protected function generateFilename(ClassDefinition $definition): string
    {
        return strtr(
            $definition->ref->getFileName(),
            [
                '/src/' => '/tests/factories/',
                '.php'  => 'Factory.php',
            ]
        );
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->fileSystem->get(
            sprintf('%s/private/Template/%s', $this->input->getOption('rootDir'), $this->getTemplateFilename())
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return array
     */
    protected function getTemplateReplacers(ClassDefinition $definition): array
    {
        return [
            '__FACTORY_NAMESPACE__' => $definition->ref->getNamespaceName(),
            '__NAMESPACE__'         => $definition->ref->getNamespaceName(),
            '__NAME__'              => $definition->ref->getShortName(),
            '__COMMENTS__'          => $this->createComments($definition)
                ->implode("\n * "),
        ];
    }
}
