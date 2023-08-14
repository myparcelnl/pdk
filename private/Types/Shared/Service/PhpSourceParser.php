<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Service;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\HasLogging;
use MyParcelNL\Sdk\src\Support\Str;
use Nette\Loaders\RobotLoader;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

final class PhpSourceParser
{
    use ParsesPhpDocs;
    use HasLogging;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    protected $definitions;

    /**
     * @var \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor
     */
    private $reflectionExtractor;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    public function __construct()
    {
        $this->typeParser          = new PhpTypeParser();
        $this->definitions         = new ClassDefinitionCollection();
        $this->reflectionExtractor = $this->reflectionExtractor ?? new ReflectionExtractor();
    }

    /**
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    public function getDefinitions(): ClassDefinitionCollection
    {
        return $this->definitions;
    }

    /**
     * @param  string $directory
     *
     * @return void
     * @throws \ReflectionException
     */
    public function parseDirectory(string $directory): void
    {
        $time = $this->getTime();
        $dir  = realpath($directory);
        $this->log('⏳', "Parsing $dir...");

        $loader = new RobotLoader();
        $loader->addDirectory($directory);

        // Scans directories for classes / interfaces / traits
        $loader->rebuild();

        $filenames = $loader->getIndexedClasses();
        ksort($filenames);

        $this->parseClassNames($filenames);

        $this->log('✅', sprintf("Parsed $dir in %s", $this->printTimeSince($time)));
    }

    /**
     * @param  string $name
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getDefinitionByName(string $name): array
    {
        $ref = $this->getReflectionClass($name);

        $fileName = $ref->getFileName();

        $cacheKey = sprintf('%s/.cache/cd_%s', __DIR__, $fileName ? md5_file($fileName) . filemtime($fileName) : $name);

        return $this->cache($cacheKey, function () use ($ref, $name): array {
            $time = $this->getTime();

            $definition = [
                'ref'        => $ref,
                'parents'    => $this->getClassParents($ref),
                'comments'   => $this->getClassComments($ref),
                'properties' => $this->getClassProperties($ref),
                'methods'    => $this->getClassMethods($ref),
            ];

            $this->log(sprintf('✅ Parsed class %s in %s', $name, $this->printTimeSince($time)));

            return $definition;
        });
    }

    /**
     * @param  \ReflectionClass|\ReflectionMethod                     $ref
     * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty $commentRef
     *
     * @return array
     */
    protected function getDocProperties($ref, $commentRef = null): array
    {
        $resolvedRef = $commentRef ?? $ref;
        $comment     = $resolvedRef->getDocComment();

        if (! $comment) {
            return [];
        }

        preg_match_all("/@property\s+.+?\\$(\w+)/", $comment, $matches);

        return $matches[1];
    }

    /**
     * @param  string $name
     *
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    protected function getReflectionClass(string $name): ReflectionClass
    {
        return $this->cache(sprintf('reflection_class_%s', $name), function () use ($name) {
            return new ReflectionClass($name);
        });
    }

    /**
     * @param  array $filenames
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function parseClassNames(array $filenames): void
    {
        foreach (array_keys($filenames) as $filename) {
            $this->definitions->push($this->getDefinitionByName($filename));
        }
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     */
    private function getClassComments(ReflectionClass $ref): array
    {
        return $this->cache(sprintf('comments_%s', $ref->getName()), function () use ($ref) {
            $comment = $ref->getDocComment();

            if (! $comment) {
                return [];
            }

            preg_match_all('/@([\w-]+)\s+(.+)/', $comment, $matches);

            return array_map(static function ($key, $value) {
                return [
                    'key'   => $key,
                    'value' => $value,
                ];
            }, $matches[1], $matches[2]);
        });
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array[]
     */
    private function getClassMethods(ReflectionClass $ref): array
    {
        $reflectionMethods = array_filter(
            $ref->getMethods(ReflectionMethod::IS_PUBLIC),
            static function (ReflectionMethod $method) {
                return ! Str::startsWith($method->getName(), '__');
            }
        );

        return array_map(
            function (ReflectionMethod $method) use ($ref): array {
                return [
                    'name'       => $method->getName(),
                    'ref'        => $method,
                    'parameters' => $this->getPhpDocTypes($ref->getName(), $method->getName()),
                ];
            },
            $reflectionMethods
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getClassParents(ReflectionClass $ref): array
    {
        return array_values(
            array_map(function (string $name) {
                return $this->getDefinitionByName($name);
            }, Utils::getClassParentsRecursive($ref->getName()))
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     */
    private function getClassProperties(ReflectionClass $ref): array
    {
        if (! $ref->isSubclassOf(Model::class)) {
            $refProperties = $this->reflectionExtractor->getProperties($ref->getName()) ?? [];
        }

        return array_map(
            function (string $property) use ($ref) {
                $classDocProperty = Arr::first(
                    $this->getClassComments($ref),
                    static function (array $keyValue) use ($property) {
                        return $keyValue['key'] === 'property' && Str::contains($keyValue['value'], "$$property");
                    }
                );

                if ($classDocProperty) {
                    $typeString = Str::before($classDocProperty['value'], '$');
                    $uses       = $this->getUses($ref);

                    $types = $this->typeParser->convertToTypes(
                        $ref->getNamespaceName(),
                        $typeString,
                        $uses
                    );
                } else {
                    $types = $this->getPhpDocTypes($ref->getName(), $property);

                    if (empty($types)) {
                        $types = $this->getReflectionTypes($ref->getName(), $property);
                    }
                }

                return [
                    'name'  => $property,
                    'types' => $types,
                ];
            },
            array_merge($refProperties ?? [], $this->getDocProperties($ref))
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     */
    private function getUses(ReflectionClass $ref): array
    {
        $file = file_get_contents($ref->getFileName());

        preg_match_all('/use\s+(.+);/', $file, $matches);

        return $matches[1];
    }
}
