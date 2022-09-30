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
     * @param  string $docComment
     *
     * @return string
     */
    protected function extractDescriptionFromPhpDocComment(string $docComment): string
    {
        $descriptionLines = [];

        $lines = explode("\n", $docComment);

        foreach ($lines as $line) {
            $trimmedLine = trim($line, " \t/*");

            if (! $trimmedLine || 0 === strpos($trimmedLine, '@')) {
                continue;
            }

            $descriptionLines[] = $trimmedLine;
        }

        return implode(' ', $descriptionLines);
    }

    /**
     * @return resource
     */
    protected function getFileHandle()
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

        preg_match_all('#@(\w+)(?:\s+(.+))?#', $comment, $matchingTags);

        $i = 0;

        $array = [];

        $description = $this->extractDescriptionFromPhpDocComment($comment);

        if ($description) {
            $array[] = [
                'param'       => 'description',
                'name'        => 'description',
                'description' => $description,
            ];
        }

        foreach (array_filter($matchingTags[0]) as $tag) {
            $type = $matchingTags[1][$i] ?? null;

            if (in_array($type, ['param', 'type', 'property', 'var', 'return'])) {
                $value = $matchingTags[2][$i] ?? null;
                preg_match_all(
                    '/(?:(?P<type>[|\[\]<>{}:,\w\s\\\]*?)\s+)?(?P<property>\$\w+)(?:\s+(?P<description>.+))?/',
                    $value,
                    $matches
                );

                $baseProperty = str_replace('$', '', $matches['property'][0] ?? '');

                $fqClassNames = $this->getFullyQualifiedClassNames(
                    $reflection->getNamespaceName(),
                    explode('|', $matches['type'][0] ?? ''),
                    $uses[1]
                );

                $array[] = [
                    'param'       => $type,
                    'name'        => $baseProperty,
                    'types'       => $fqClassNames,
                    'description' => $matches['description'][0] ?? null,
                ];

                $i++;
                continue;
            }

            $array[] = [
                'param'       => $type,
                'name'        => $type,
                'types'       => [],
                'description' => $matchingTags[2][$i] ?? null,
            ];

            $i++;
        }

        $pattern = "#@([a-zA-Z]+)\s+(?:([|\[\]<>{}:,\w\s\\\]*?)\s+)?(\\$\w+)(?:\s+(.+))?#";
        preg_match_all($pattern, $comment, $matchingTags);

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
