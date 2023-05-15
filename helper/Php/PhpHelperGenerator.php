<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Php;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Helper\Shared\Collection\TypeCollection;
use MyParcelNL\Pdk\Helper\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Helper\Shared\Service\PhpTypeParser;
use MyParcelNL\Sdk\src\Support\Str;
use Symfony\Component\PropertyInfo\Type;

final class PhpHelperGenerator extends AbstractHelperGenerator
{
    protected const FILE_NAME = BASE_DIR . '/types/pdk_ide_helper.php';

    /**
     * @var \MyParcelNL\Pdk\Helper\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    /**
     * @param  \MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection $definitions
     */
    public function __construct(ClassDefinitionCollection $definitions)
    {
        parent::__construct($definitions);

        $this->typeParser = new PhpTypeParser();
    }

    /**
     * @return void
     */
    protected function generate(): void
    {
        $handle = $this->getHandle(self::FILE_NAME);

        $lines = [
            '<?php /** @noinspection ALL */',
            '',
        ];

        foreach ($this->definitions->all() as $definition) {
            [$modelGetters, $modelSetters, $modelProperties] = $this->getModelProperties($definition);

            $lines[] = "namespace {$definition->ref->getNamespaceName()};";
            $lines[] = '';
            $lines[] = '/**';

            foreach (array_merge($modelProperties, $modelGetters, $modelSetters) as $property) {
                $lines[] = " * $property";
            }

            $lines[] = ' */';
            $lines[] = "class {$definition->ref->getShortName()} { }";
            $lines[] = '';
        }

        $this->writeLines($handle, $lines);
        $this->close($handle, self::FILE_NAME);
    }

    /**
     * @return string[]
     */
    protected function getAllowedClasses(): array
    {
        return [/*Model::class,*/ Collection::class];
    }

    /**
     * @param  \MyParcelNL\Pdk\Helper\Shared\Model\ClassDefinition $definition
     *
     * @return array[]
     */
    private function getModelProperties(ClassDefinition $definition): array
    {
        $isCollection = $definition->isSubclassOf(Collection::class);

        $modelGetters    = [];
        $modelSetters    = [];
        $modelProperties = [];

        /** @var \MyParcelNL\Pdk\Helper\Shared\Model\ClassProperty $property */
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

        return [$modelGetters, $modelSetters, $modelProperties];
    }

    /**
     * @param  \MyParcelNL\Pdk\Helper\Shared\Collection\TypeCollection $types
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
                $singleType = sprintf("%s ", $this->typeParser->getTypeAsString($type));
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
