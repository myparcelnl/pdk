<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use Nette\Loaders\RobotLoader;
use ReflectionClass;
use RuntimeException;

abstract class AbstractHelperGenerator
{
    /**
     * @var array{reflectionClass: \ReflectionClass, properties: array{name: string, types: string[]}[]}[]
     */
    protected $data = [];

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
     */
    public function generate(): void
    {
        echo '    Generating helper ' . static::class . PHP_EOL;
        $this->parseSource();

        $this->write();

        $this->close();

        $path = realpath($this->getFileName());
        echo " ✅  Wrote to $path" . PHP_EOL;
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
                || in_array($type, ['array', 'string', 'bool', 'int', 'null'])
                || Str::startsWith($type, '\\')) {
                $newTypes[] = $type;
                continue;
            }

            $bareType = str_replace('[]', '', $type);

            $match = array_filter($uses, static function (string $use) use ($bareType, $type) {
                $parts = explode('\\', $use);
                return array_pop($parts) === $bareType;
            });

            $newType = sprintf("\\%s", $match[0] ?? "$namespace\\$type");

            $newTypes[] = str_replace($bareType, $newType, $type);
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
     * @param  \ReflectionClass|\ReflectionMethod $reflection
     *
     * @return array
     */
    protected function parseDocComment($reflection): array
    {
        $uses = [];

        if ($reflection->getFileName()) {
            $fileContents = file_get_contents($reflection->getFileName());

            preg_match_all('/^use\s+(.+);$/m', $fileContents, $uses);
        }

        $comment = $reflection->getDocComment();

        if (! $comment) {
            return [];
        }

        $pattern = "#@([a-zA-Z]+)\s+([|\[\]<>{}:,\w\s\\\]*?)\s+(\\$\w+)#";
        preg_match_all($pattern, $comment, $matches);

        $i     = 0;
        $array = [];

        foreach ($matches[3] as $property) {
            $baseProperty = str_replace('$', '', $property);

            $types        = explode('|', $matches[2][$i]);
            $fqClassNames = $this->getFullyQualifiedClassNames(
                $reflection->getNamespaceName(),
                $types,
                $uses[1]
            );

            $array[] = [
                'param' => $matches[1][$i],
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

        foreach (array_keys($classNames) as $class) {
            $parents = Utils::getClassParentsRecursive($class);

            if (count($this->getWhitelistClasses())) {
                $relevantParents = array_intersect($parents, $this->getWhitelistClasses());
                if (empty($relevantParents)) {
                    continue;
                }
            }

            $classes[] = ['name' => $class, 'parents' => $parents];
        }

        $this->parseClassesPhpDocs($classes);
    }

    /**
     * @param $classes
     *
     * @return void
     * @throws \ReflectionException
     */
    private function parseClassesPhpDocs($classes): void
    {
        foreach ($classes as $class) {
            $className              = $class['name'];
            $reflectionClass        = new ReflectionClass($className);
            $this->data[$className] = [
                'name'            => $className,
                'parents'         => $class['parents'],
                'reflectionClass' => $reflectionClass,
                'properties'      => $this->parseDocComment($reflectionClass),
            ];
        }
    }
}
