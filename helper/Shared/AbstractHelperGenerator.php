<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
use Nette\Loaders\RobotLoader;
use ReflectionClass;
use RuntimeException;
use Throwable;

abstract class AbstractHelperGenerator
{
    /**
     * @var array{reflectionClass: \ReflectionClass, properties: array{name: string, types: string[]}[]}[]
     */
    protected $data = [];

    /**
     * @var ReflectionClass[]
     */
    protected $reflectionCache = [];

    /**
     * @var resource
     */
    private $handle;

    /**
     * @return string
     */
    abstract protected function getFileName(): string;

    /**
     * @return void
     */
    abstract protected function write(): void;

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function generate(): void
    {
        echo '    Generating helper ' . static::class . PHP_EOL;
        try {
            $this->parseSource();

            $this->write();

            $this->close();

            $path = realpath($this->getFileName());
            echo " ✅  Wrote to $path" . PHP_EOL;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        if (! $this->handle) {
            $outputFile = $this->getFileName();
            $directory  = dirname($outputFile);

            if (! is_dir($directory) && ! mkdir($concurrentDirectory = $directory) && ! is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            $this->handle = fopen($outputFile, 'wb+');
        }

        return $this->handle;
    }

    /**
     * @param  \ReflectionClass $class
     * @param  array            $parents
     *
     * @return bool
     */
    protected function classAllowed(ReflectionClass $class, array $parents): bool
    {
        return true;
    }

    /**
     * @param  string   $namespace
     * @param  string[] $types
     * @param  string[] $uses
     *
     * @return array
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

    /**
     * @return string[]
     */
    protected function getWhitelistClasses(): array
    {
        return [Model::class, Collection::class];
    }

    /**
     * @param  array $classNames
     * @param  array $classes
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function parseClassNames(array $classNames, array $classes): void
    {
        foreach (array_keys($classNames) as $class) {
            $ref     = new ReflectionClass($class);
            $parents = Utils::getClassParentsRecursive($class);

            if (! $this->classAllowed($ref, $parents)) {
                continue;
            }

            $whitelistClasses = $this->getWhitelistClasses();

            if (count($whitelistClasses) && ! in_array($class, $whitelistClasses, true)) {
                $relevantParents = array_intersect($parents, $whitelistClasses);

                if (empty($relevantParents)) {
                    continue;
                }
            }

            $classes[] = ['class' => $ref, 'parents' => $parents];
        }

        $this->parseClassesPhpDocs($classes);
    }

    /**
     * @param  \ReflectionClass|\ReflectionMethod                     $reflection
     * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty $commentRef
     *
     * @return array
     */
    protected function parseDocComment($reflection, $commentRef = null): array
    {
        $commentRef = $commentRef ?? $reflection;
        $uses       = [];

        if ($reflection->getFileName()) {
            $fileContents = file_get_contents($reflection->getFileName());

            preg_match_all('/^use\s+(.+);$/m', $fileContents, $uses);
        }

        $comment = $commentRef->getDocComment();

        if (! $comment) {
            return [];
        }

        $pattern = "/@([A-z]+)\s+([\\\|<>\w\s{,:}\[\]]+)\s*(\\$\w+)/";
        preg_match_all($pattern, $comment, $matches);

        $i     = 0;
        $array = [];

        foreach ($matches[3] as $property) {
            $baseProperty = str_replace('$', '', trim($property));

            $types        = explode('|', trim($matches[2][$i]));
            $fqClassNames = $this->getFullyQualifiedClassNames(
                $reflection->getNamespaceName(),
                $types,
                $uses[1]
            );

            $array[] = [
                'param' => trim($matches[1][$i]),
                'name'  => $baseProperty,
                'types' => $fqClassNames,
            ];

            $i++;
        }

        return $array;
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function parseSource(): void
    {
        $loader = new RobotLoader();
        $loader->addDirectory(BASE_DIR . '/src');

        // Scans directories for classes / interfaces / traits
        $loader->rebuild();

        $classes = [];

        $classNames = $loader->getIndexedClasses();
        ksort($classNames);

        $this->parseClassNames($classNames, $classes);
    }

    /**
     * @param  array $classes
     *
     * @return void
     */
    private function parseClassesPhpDocs(array $classes): void
    {
        foreach ($classes as $item) {
            /** @var ReflectionClass $ref */
            $ref  = $item['class'];
            $name = $ref->getShortName();

            $this->reflectionCache[$ref->getName()] = $ref;

            $properties = $this->parseDocComment($ref);

            $this->data[$name] = [
                'name'            => $name,
                'class'           => $ref->getName(),
                'parents'         => $item['parents'],
                'reflectionClass' => $ref,
                'properties'      => $properties,
            ];
        }
    }
}
