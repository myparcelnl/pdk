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
    /**
     * @var \MyParcelNL\Pdk\Console\GenerateFactory\Service\ModelFactoryGeneratorService
     */
    private $modelService;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface                                     $fileSystem
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser                 $sourceParser
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser                   $typeParser
     * @param  \MyParcelNL\Pdk\Console\GenerateFactory\Service\ModelFactoryGeneratorService $modelService
     */
    public function __construct(
        FileSystemInterface          $fileSystem,
        PhpSourceParser              $sourceParser,
        PhpTypeParser                $typeParser,
        ModelFactoryGeneratorService $modelService
    ) {
        parent::__construct($fileSystem, $sourceParser, $typeParser);
        $this->modelService = $modelService;
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function createComments(ClassDefinition $definition): Collection
    {
        return new Collection([sprintf('@method %s make()', $definition->ref->getShortName())]);
    }

    /**
     * @return class-string
     */
    protected function getClass(): string
    {
        return Collection::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return null|\MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition
     */
    protected function getModelDefinition(ClassDefinition $definition): ?ClassDefinition
    {
        $collectionValueType = $definition->getCollectionValueType();

        try {
            return $this->sourceParser->getDefinitionByName($collectionValueType);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    protected function getTemplateFilename(): string
    {
        return 'CollectionFactory.php.stub';
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return array
     */
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
