<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Php;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Sdk\src\Support\Str;

final class PhpHelperGenerator extends AbstractHelperGenerator
{
    protected function getFileName(): string
    {
        return BASE_DIR . '/types/pdk_ide_helper.php';
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function write(): void
    {
        fwrite($this->getHandle(), '<?php /** @noinspection ALL */' . str_repeat(PHP_EOL, 2));

        foreach ($this->data as $data) {
            $ref        = $data['reflectionClass'];
            $properties = $data['properties'];
            $parents    = $data['parents'];

            [$modelGetters, $modelSetters, $modelProperties] = $this->getModelProperties($parents, $properties);

            $propertyList = sprintf(
                ' * %s',
                implode(
                    PHP_EOL . ' * ',
                    array_merge($modelProperties, $modelGetters, $modelSetters)
                )
            );

            fwrite(
                $this->getHandle(),
                <<<EOF

namespace {$ref->getNamespaceName()};

/**
$propertyList
 */
class {$ref->getShortName()} { }

EOF
            );
        }
    }

    /**
     * @param  array $parents
     * @param  array $properties
     *
     * @return array[]
     */
    private function getModelProperties(array $parents, array $properties): array
    {
        $isCollection = in_array(Collection::class, $parents, true);

        $modelGetters    = [];
        $modelSetters    = [];
        $modelProperties = [];

        foreach ($properties as $property) {
            $baseProperty       = $property['name'];
            $types              = $property['types'];
            $typeHint           = $this->getTypeHint($types);
            $fqClassNamesString = implode('|', $types);

            if (! $isCollection) {
                $getter = Str::camel('get_' . $baseProperty);
                $setter = Str::camel(sprintf('set_%s_attribute', $baseProperty));

                $modelGetters[] = "@method $fqClassNamesString $getter()";
                $modelSetters[] = "@method $fqClassNamesString $setter($typeHint$$baseProperty)";
            }

            if ($isCollection && 'items' === $baseProperty) {
                $singleTypeHint = str_replace('[]', '', $typeHint);

                $modelGetters[] = "@method {$typeHint}all()";
                $modelGetters[] = "@method {$typeHint}filter(callable \$callback = null)";
                $modelGetters[] = "@method {$singleTypeHint}first(callable \$callback = null)";
                $modelGetters[] = "@method {$singleTypeHint}firstWhere(string \$key, mixed \$operator, mixed \$value = null)";
                $modelGetters[] = '@method mixed map(callable $callback = null)';
                $modelGetters[] = "@method {$singleTypeHint}pop()";
                $modelGetters[] = "@method {$singleTypeHint}shift()";
            }

            $modelProperties[] = "@property $fqClassNamesString $$baseProperty";
        }

        return [$modelGetters, $modelSetters, $modelProperties];
    }

    /**
     * @param $types
     *
     * @return string
     */
    private function getTypeHint($types): string
    {
        $singleType = '';

        if (count($types) > 1) {
            $collectionTypes = array_values(
                array_filter($types, static function ($type) {
                    return class_exists($type)
                        && in_array(
                            Collection::class,
                            Utils::getClassParentsRecursive($type),
                            true
                        );
                })
            );

            $nullTypes = array_filter($types, static function ($type) {
                return 'null' === $type;
            });

            if (! empty($collectionTypes)) {
                $singleType = "$collectionTypes[0] ";
            }

            if (! empty($nullTypes)) {
                $singleType = "?$singleType";
            }

            return $singleType;
        }

        return "$types[0] ";
    }
}
