<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Service;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use Symfony\Component\PropertyInfo\Type;

class PhpTypeParser
{
    /**
     * @return \Symfony\Component\PropertyInfo\Type[]
     */
    public function convertToTypes(string $namespace, string $typeString, array $uses = []): array
    {
        $isCollection = Str::contains($typeString, '[]');

        $parts = array_map(static fn(string $item) => Str::before(trim($item), '[]'), explode('|', $typeString));

        $filtered = array_filter($parts, static fn(string $item) => 'null' !== $item);

        $nullable = count($filtered) !== count($parts);

        $classNames = $this->getFullyQualifiedClassNames($namespace, $filtered, $uses);

        return array_map(fn(string $typeString) => $this->createType($typeString, $nullable, $isCollection),
            $classNames);
    }

    public function createFqcn(Type $type): string
    {
        return $type ? sprintf('%s ', $type->getName()) : '';
    }

    public function createType(string $typeString, bool $nullable = false, bool $asCollection = false): Type
    {
        if ($asCollection) {
            return new Type(
                'array',
                $nullable,
                null,
                true,
                new Type('string'),
                $this->createType($typeString)
            );
        }

        if (in_array($typeString, Type::$builtinTypes, true)) {
            return new Type($typeString, $nullable);
        }

        if (! Str::startsWith($typeString, '\\')) {
            $typeString = sprintf('\\%s', $typeString);
        }

        return new Type('object', $nullable, $typeString);
    }

    public function extendsCollection(Type $type): bool
    {
        $typeClass = $type->getClassName();

        return $typeClass
            && class_exists($typeClass)
            && in_array(
                Collection::class,
                Utils::getClassParentsRecursive($typeClass),
                true
            );
    }

    /**
     * @param  null|\Symfony\Component\PropertyInfo\Type $type
     */
    public function getTypeAsString(?Type $type): string
    {
        if (! $type) {
            return 'mixed';
        }

        $string = $type->getClassName() ?: $type->getBuiltinType();

        if ($type->isCollection()) {
            return sprintf(
                '%s[]',
                $this->getTypeAsString($type->getCollectionValueTypes()[0])
            );
        }

        if ($string === 'class-string') {
            return 'string';
        }

        return $string;
    }

    /**
     * @param  string[] $types
     * @param  string[] $uses
     */
    protected function getFullyQualifiedClassNames(string $namespace, array $types, array $uses): array
    {
        $newTypes = [];

        foreach ($types as $type) {
            if (Str::startsWith($type, 'array<')
                || Str::startsWith($type, 'array{')
                || in_array($type, ['array', 'string', 'bool', 'int', 'null'])
                || Str::startsWith($type, '\\')) {
                $newTypes[] = $type;
                continue;
            }

            $bareType = str_replace('[]', '', $type);

            $match = array_filter($uses, static function (string $use) use ($bareType) {
                $parts = explode('\\', $use);
                return Arr::last($parts) === $bareType;
            });

            $newTypes[] = sprintf("\\%s", Arr::first($match, null, "$namespace\\$type"));
        }

        return $newTypes;
    }
}
