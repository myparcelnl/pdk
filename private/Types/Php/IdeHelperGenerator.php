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
use MyParcelNL\Sdk\src\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyInfo\Type;

final class IdeHelperGenerator extends AbstractHelperGenerator
{
    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    public function __construct(
        InputInterface            $input,
        OutputInterface           $output,
        ClassDefinitionCollection $definitions,
        string                    $baseDir
    ) {
        parent::__construct($input, $output, $definitions, $baseDir);

        $this->typeParser = Pdk::get(PhpTypeParser::class);
    }

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
                $properties = array_map(static fn(KeyValue $comment) => "@$comment->key $comment->value",
                    $definition->comments->all());
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

    private function getFilename(): string
    {
        return "$this->baseDir/.meta/pdk_ide_helper.php";
    }

    /**
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

        return [...$modelGetters, ...$modelSetters, ...$modelProperties];
    }

    private function getTypeHint(TypeCollection $types): string
    {
        $singleType = null;

        if ($types->count() > 1) {
            $collectionTypes = $types->filter(fn(Type $type) => $this->typeParser->extendsCollection($type));

            $nullTypes = $types->filter(static fn(Type $type) => $type->isNullable());

            if ($collectionTypes->isNotEmpty()) {
                /** @var Type $type */
                $type       = $collectionTypes->first();
                $singleType = sprintf('%s ', $this->typeParser->getTypeAsString($type));
            }

            if ($nullTypes->isNotEmpty()) {
                $singleType = "?$singleType";
            }
        }

        $singleType ??= $this->typeParser->getTypeAsString($types->first());

        if (Str::contains($singleType, '{')) {
            $singleType = Str::before($singleType, '{');
        } elseif (Str::contains($singleType, '<')) {
            $singleType = Str::before($singleType, '<');
        }

        return $singleType . ' ';
    }
}
