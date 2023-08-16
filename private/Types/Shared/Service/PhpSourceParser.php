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
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassMethod;
use MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

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
            $time = $this->getTime();

            $definition = [
                'ref'        => [
                    'name' => $ref->getName(),
                ],
                'parents'    => $this->getClassParents($ref)
                    ->toStorableArray(),
                'comments'   => $this->getClassComments($ref)
                    ->toStorableArray(),
                'properties' => $this->getClassProperties($ref)
                    ->toStorableArray(),
                'methods'    => $this->getClassMethods($ref)
                    ->toStorableArray(),
            ];

            if ($this->output->isVerbose()) {
                $this->output->writeln(sprintf('✅ Parsed class %s in %s', $name, $this->printTimeSince($time)));
            }

            return $definition;
        });

        return new ClassDefinition($cached);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $files
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    public function parse(Collection $files): ClassDefinitionCollection
    {
        $time = $this->getTime();
        $this->output->writeln('⏳ Parsing files...');

        $result = $this->parseFiles($files);

        $this->output->writeln(sprintf('✅ Parsed files in %s', $this->printTimeSince($time)));

        if ($result->isEmpty()) {
            $this->output->writeln('<error>No files found</error>');
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

        return sprintf(
            '%s.tmp/console/_%s.txt',
            Pdk::get('rootDir'),
            $fileName
                ? md5_file($fileName) . filemtime($fileName)
                : Str::snake($reflectionClass->getName())
        );
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
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\KeyValueCollection
     */
    private function getClassComments(ReflectionClass $ref): KeyValueCollection
    {
        $comment = $ref->getDocComment();

        if (! $comment) {
            return new KeyValueCollection();
        }

        preg_match_all('/@([\w-]+)\s+(.+)/', $comment, $matches);

        return new KeyValueCollection(
            array_map(static function ($key, $value) {
                return [
                    'key'   => $key,
                    'value' => $value,
                ];
            }, $matches[1], $matches[2])
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassMethodCollection
     */
    private function getClassMethods(ReflectionClass $ref): ClassMethodCollection
    {
        $reflectionMethods = array_filter(
            $ref->getMethods(ReflectionMethod::IS_PUBLIC),
            static function (ReflectionMethod $reflectionMethod) {
                return ! Str::startsWith($reflectionMethod->getName(), '__');
            }
        );

        $items = array_map(
            function (ReflectionMethod $reflectionMethod) use ($ref): ClassMethod {
                $name = $reflectionMethod->getName();

                return new ClassMethod([
                    'name'       => $name,
                    'ref'        => [
                        'name'  => $name,
                        'class' => $ref->getName(),
                    ],
                    'parameters' => $this->getPhpDocTypes($ref->getName(), $name),
                ]);
            },
            $reflectionMethods
        );

        return new ClassMethodCollection($items);
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     * @throws \ReflectionException
     */
    private function getClassParents(ReflectionClass $ref): ClassDefinitionCollection
    {
        return new ClassDefinitionCollection(
            array_values(
                array_map(function (string $name): ClassDefinition {
                    return $this->getDefinitionByName($name);
                }, Utils::getClassParentsRecursive($ref->getName()))
            )
        );
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassPropertyCollection
     */
    private function getClassProperties(ReflectionClass $ref): ClassPropertyCollection
    {
        if (! $ref->isSubclassOf(Model::class)) {
            $refProperties = $this->reflectionExtractor->getProperties($ref->getName()) ?? [];
        }

        return new ClassPropertyCollection(
            array_map(
                function (string $property) use ($ref) {
                    $classDocProperty = $this->getClassComments($ref)
                        ->first(
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
            )
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
