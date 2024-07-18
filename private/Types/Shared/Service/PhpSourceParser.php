<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Service;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Concern\HasCommandContext;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassMethodCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassPropertyCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\KeyValueCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\ReportsTiming;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue;
use MyParcelNL\Sdk\src\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use function array_map;

final class PhpSourceParser
{
    use HasCommandContext;
    use ReportsTiming;
    use ParsesPhpDocs;

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystem
     */
    private $fileSystem;

    /**
     * @var \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor
     */
    private $reflectionExtractor;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser
     */
    private $typeParser;

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpTypeParser $typeParser
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface                   $fileSystem
     */
    public function __construct(PhpTypeParser $typeParser, FileSystemInterface $fileSystem)
    {
        $this->typeParser = $typeParser;
        $this->fileSystem = $fileSystem;

        $this->reflectionExtractor = $this->reflectionExtractor ?? new ReflectionExtractor();
    }

    /**
     * @param  string $name
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition
     * @throws \ReflectionException
     */
    public function getDefinitionByName(string $name): ClassDefinition
    {
        $ref = new ReflectionClass($name);

        $cached = $this->fileCache($this->getCacheKey($ref), function () use ($ref, $name): array {
            $time       = $this->getTime();
            $definition = [
                'parents'    => $this->getClassParents($ref),
                'comments'   => $this->getClassComments($ref),
                'properties' => $this->getClassProperties($ref),
                'methods'    => $this->getClassMethods($ref),
            ];

            if ($this->output->isVerbose()) {
                $this->log(sprintf('✅ Parsed class %s in %s', $name, $this->printTimeSince($time)));
            }

            return $definition;
        });

        $parentDefinitions = array_map(function (string $name) {
            return $this->getDefinitionByName($name);
        }, $cached['parents']);

        return new ClassDefinition([
            'ref'        => [
                'name' => $ref->getName(),
            ],
            'parents'    => new ClassDefinitionCollection($parentDefinitions),
            'comments'   => new KeyValueCollection($cached['comments']),
            'properties' => new ClassPropertyCollection($cached['properties']),
            'methods'    => new ClassMethodCollection($cached['methods']),
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $files
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    public function parse(Collection $files): ClassDefinitionCollection
    {
        $this->readsFromCache = $this->input->getArgument('readCache') ?? true;
        $this->writesToCache  = $this->input->getArgument('writeCache') ?? true;

        $time = $this->getTime();
        $this->log('⏳ Parsing files...');

        $result = $this->parseFiles($files);

        $this->log(sprintf('✅ Parsed files in %s', $this->printTimeSince($time)));

        if ($result->isEmpty()) {
            $this->log('<error>No files found</error>');
        }

        return $result;
    }

    /**
     * @param  \ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function getCacheKey(ReflectionClass $reflectionClass): string
    {
        $fileName = $reflectionClass->getFileName();

        return $fileName
            ? md5_file($fileName) . filemtime($fileName)
            : Str::snake($reflectionClass->getName());
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
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $filenames
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    protected function parseFiles(Collection $filenames): ClassDefinitionCollection
    {
        return new ClassDefinitionCollection(
            $filenames->map(function (string $filename) {
                return $this->getDefinitionByName($filename);
            })
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     */
    private function getClassComments(ReflectionClass $ref): array
    {
        $comment = $ref->getDocComment();

        return $this->fileCache("comments_{$ref->getName()}", function () use ($comment) {
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
     * @return array
     */
    private function getClassMethods(ReflectionClass $ref): array
    {
        $reflectionMethods = array_filter(
            $ref->getMethods(ReflectionMethod::IS_PUBLIC),
            static function (ReflectionMethod $reflectionMethod) {
                return ! Str::startsWith($reflectionMethod->getName(), '__');
            }
        );

        return array_map(
            function (ReflectionMethod $reflectionMethod) use ($ref) {
                $name = $reflectionMethod->getName();

                return [
                    'name'       => $name,
                    'ref'        => [
                        'name'  => $name,
                        'class' => $ref->getName(),
                    ],
                    'parameters' => $this->getPhpDocTypes($ref->getName(), $name),
                ];
            },
            $reflectionMethods
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return array
     */
    private function getClassParents(ReflectionClass $ref): array
    {
        return array_values(
            array_map(static function (string $name) {
                return $name;
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
                $classComments = new KeyValueCollection($this->getClassComments($ref));

                $classDocProperty = $classComments->first(
                    static function (KeyValue $item) use ($property) {
                        return 'property' === $item->key && Str::contains($item->value, "$$property");
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
        $file = $this->fileSystem->get($ref->getFileName());

        preg_match_all('/use\s+(.+);/', $file, $matches);

        return $matches[1];
    }
}
