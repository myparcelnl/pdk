<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\GenerateFactory\Service;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;
use MyParcelNL\Pdk\Tests\Factory\Exception\NotImplementedException;
use Throwable;

final class CollectionFactoryGeneratorService extends AbstractFactoryGeneratorService
{
    public function __construct(
        FileSystemInterface                           $fileSystem,
        PhpSourceParser                               $sourceParser,
        PhpTypeParser                                 $typeParser,
        private readonly ModelFactoryGeneratorService $modelService
    ) {
        parent::__construct($fileSystem, $sourceParser, $typeParser);
    }

    protected function createComments(ClassDefinition $definition): Collection
    {
        return new Collection([
            sprintf('@template T of %s', $definition->ref->getShortName()),
            sprintf('@method %s make()', $definition->ref->getShortName()),
        ]);
    }

    /**
     * @return class-string
     */
    protected function getClass(): string
    {
        return Collection::class;
    }

    protected function getModelDefinition(ClassDefinition $definition): ?ClassDefinition
    {
        $collectionValueType = $definition->getCollectionValueType();

        try {
            return $this->sourceParser->getDefinitionByName($collectionValueType);
        } catch (Throwable) {
            return null;
        }
    }

    protected function getTemplateFilename(): string
    {
        return 'CollectionFactory.php.stub';
    }

    protected function getTemplateReplacers(ClassDefinition $definition): array
    {
        $modelDefinition = $this->getModelDefinition($definition);

        $useClass       = NotImplementedException::class;
        $implementation = sprintf('throw new %s();', Utils::classBasename($useClass));

        if ($modelDefinition) {
            $modelFactoryNamespace = $this->modelService->createNamespace($modelDefinition);
            $modelFactoryName      = "{$modelDefinition->ref->getShortName()}Factory";

            $implementation = "return $modelFactoryName::class;";
            $useClass       = "$modelFactoryNamespace\\$modelFactoryName";
        }

        return array_merge(
            parent::getTemplateReplacers($definition), [
                '__MODEL_FACTORY_USE__' => sprintf('use %s;', $useClass),
                '__GET_MODEL_FACTORY__' => $implementation,
            ]
        );
    }
}
