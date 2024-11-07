<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\GenerateFactory\Service;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassProperty;
use MyParcelNL\Pdk\Tests\Factory\FactoryFactory;
use MyParcelNL\Pdk\Base\Support\Str;
use Throwable;

final class ModelFactoryGeneratorService extends AbstractFactoryGeneratorService
{
    /**
     * @param  string                                                         $class
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection $types
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection
     */
    protected function addFactoryTypes(string $class, TypeCollection $types): TypeCollection
    {
        try {
            $definition = $this->sourceParser->getDefinitionByName($class);

            if ($definition->isSubclassOf(Model::class)) {
                $modelFactory = FactoryFactory::create($definition->ref->getName());

                return $types->push(
                    $this->typeParser->createType('array'),
                    $this->typeParser->createType(get_class($modelFactory))
                );
            }

            if ($definition->isSubclassOf(Collection::class)) {
                $collectionValueType = $definition->getCollectionValueType();

                if (! $collectionValueType) {
                    return $types;
                }

                $modelFactory = FactoryFactory::create($collectionValueType);

                return $types->push(
                    $this->typeParser->createType('array', false, true),
                    $this->typeParser->createType(get_class($modelFactory), false, true)
                );
            }
        } catch (Throwable $e) {
            // Silently ignore
        }

        return $types;
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function createComments(ClassDefinition $definition): Collection
    {
        return (new Collection($definition->properties->all()))
            ->map(function (ClassProperty $property) {
                $types       = $this->getPropertyTypes($property);
                $typesString = (string) $types;

                return sprintf(
                    '@method $this with%s(%s$%s)',
                    Str::studly($property->name),
                    $typesString ? "$typesString " : '',
                    Str::camel($property->name)
                );
            })
            ->sort()
            ->prepend(sprintf('@method %s make()', $definition->ref->getShortName()))
            ->prepend(sprintf('@template T of %s', $definition->ref->getShortName()));
    }

    /**
     * @return string
     */
    protected function getClass(): string
    {
        return Model::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassProperty $property
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection
     */
    protected function getPropertyTypes(ClassProperty $property)
    {
        $types = $property->types;

        /** @var \Symfony\Component\PropertyInfo\Type $firstType */
        $firstType = $types->first();

        $class = $firstType ? $firstType->getClassName() : $firstType;

        if (! $class) {
            return $types;
        }

        if (Str::startsWith($class, 'array')) {
            $property->types->shift();
        }

        return $this->addFactoryTypes($class, $types);
    }

    /**
     * @return string
     */
    protected function getTemplateFilename(): string
    {
        return 'ModelFactory.php.stub';
    }
}
