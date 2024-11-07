<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Php;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Base\Support\Str;
use Symfony\Component\PropertyInfo\Type;

final class IdeHelperGenerator extends AbstractHelperGenerator
{
    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection $definitions
     * @param  string                                                                    $baseDir
     */
    public function __construct(ClassDefinitionCollection $definitions, string $baseDir)
    {
        parent::__construct($definitions, $baseDir);

        $this->typeParser = Pdk::get(PhpTypeParser::class);
    }

    /**
     * @return void
     */
    protected function generate(): void
    {
        $handle = $this->getHandle($this->getFilename());

        $lines = [
            '<?php /** @noinspection ALL */',
            '',
        ];

        /** @var ClassDefinition $definition */
        foreach ($this->definitions->all() as $definition) {
            if ($definition->isSubclassOf(Facade::class)) {
                $properties = array_map(static function (KeyValue $comment) {
                    return "@$comment->key $comment->value";
                }, $definition->comments->all());
            } else {
                $properties = $this->getModelProperties($definition);
            }

            $lines[] = "namespace {$definition->ref->getNamespaceName()};";
            $lines[] = '';
            $lines[] = '/**';

            foreach ($properties as $property) {
                $lines[] = " * $property";
            }

            $lines[] = ' */';
            $lines[] = "class {$definition->ref->getShortName()} { }";
            $lines[] = '';
        }

        $this->writeLines($handle, $lines);
        $this->close($handle, $this->getFilename());
    }

    /**
     * @return string[]
     */
    protected function getAllowedClasses(): array
    {
        return [Model::class, Collection::class, Facade::class];
    }

    /**
     * @return string
     */
    private function getFilename(): string
    {
        return "$this->baseDir/.meta/pdk_ide_helper.php";
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return string[]
     */
    private function getModelProperties(ClassDefinition $definition): array
    {
        $isCollection = $definition->isSubclassOf(Collection::class);

        $modelGetters    = [];
        $modelSetters    = [];
        $modelProperties = [];

        /** @var \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassProperty $property */
        foreach ($definition->properties->all() as $property) {
            $propertyName = $property->name;

            $fqClassNamesString = implode('|', $property->types->getNames());
            $typeHint           = $this->getTypeHint($property->types);

            if (! $isCollection) {
                $strippedFqcn = preg_replace('/<.+>|\{.+}/', '', $fqClassNamesString);
                $getter       = Str::camel("get_$propertyName");
                $setter       = Str::camel(sprintf('set_%s_attribute', $propertyName));

                $modelGetters[] = "@method $strippedFqcn $getter()";
                $modelSetters[] = "@method $strippedFqcn $setter($typeHint\$$propertyName)";
            }

            if ($isCollection) {
                if ('items' === $propertyName) {
                    $singleTypeHint = str_replace('[]', '', $typeHint);

                    $modelProperties[] = trim("@property $fqClassNamesString $$propertyName");
                    $modelGetters[]    = "@method {$typeHint}all()";
                    $modelGetters[]    = "@method {$typeHint}filter(callable \$callback = null)";
                    $modelGetters[]    = "@method {$singleTypeHint}first(callable \$callback = null)";
                    $modelGetters[]    = "@method {$singleTypeHint}firstWhere(string \$key, mixed \$operator, mixed \$value = null)";
                    $modelGetters[]    = '@method mixed map(callable $callback = null)';
                    $modelGetters[]    = "@method {$singleTypeHint}pop()";

                    $modelGetters[] = "@method {$singleTypeHint}shift()";
                }
            } else {
                $modelProperties[] = trim("@property $fqClassNamesString $$propertyName");
            }
        }

        return array_merge($modelGetters, $modelSetters, $modelProperties);
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection $types
     *
     * @return string
     */
    private function getTypeHint(TypeCollection $types): string
    {
        $singleType = null;

        if ($types->count() > 1) {
            $collectionTypes = $types->filter(function (Type $type) {
                return $this->typeParser->extendsCollection($type);
            });

            $nullTypes = $types->filter(static function (Type $type) {
                return $type->isNullable();
            });

            if ($collectionTypes->isNotEmpty()) {
                /** @var Type $type */
                $type       = $collectionTypes->first();
                $singleType = sprintf('%s ', $this->typeParser->getTypeAsString($type));
            }

            if ($nullTypes->isNotEmpty()) {
                $singleType = "?$singleType";
            }
        }

        $singleType = $singleType ?? $this->typeParser->getTypeAsString($types->first());

        if (Str::contains($singleType, '{')) {
            $singleType = Str::before($singleType, '{');
        } elseif (Str::contains($singleType, '<')) {
            $singleType = Str::before($singleType, '<');
        }

        return $singleType . ' ';
    }
}
