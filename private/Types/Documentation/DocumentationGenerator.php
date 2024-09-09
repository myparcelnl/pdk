<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Documentation;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue;
use MyParcelNL\Pdk\Base\Support\Str;
use ReflectionClass;

final class DocumentationGenerator extends AbstractHelperGenerator
{
    private const BRANCH = 'alpha';

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return bool
     */
    protected function classAllowed(ClassDefinition $definition): bool
    {
        return $definition->ref->isInterface()
            || $definition->isSubclassOf(Facade::class);
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return string
     */
    protected function createFqcn(ReflectionClass $ref): string
    {
        return sprintf("\\%s\\%s", $ref->getNamespaceName(), $ref->getShortName());
    }

    /**
     * @return void
     */
    protected function generate(): void
    {
        $filename = sprintf('%s/docs/README.md', $this->baseDir);
        $handle   = $this->getHandle($filename);

        $lines = [];

        foreach ($this->definitions->all() as $definition) {
            $fqcn = $this->createFqcn($definition->ref);

            [$requiredIcon, $requiredFor] = $this->getRequired($definition);

            $lines[] = trim("### {$definition->ref->getShortName()} $requiredIcon");
            $lines[] = '';
            $lines[] = "[`$fqcn`]({$this->getLinkToSource($definition->ref)})";
            $lines[] = '';

            if ($requiredFor) {
                $lines[] = "Required for: $requiredFor";
                $lines[] = '';
            }

            $docComment = $this->getPlainTextFromDocComment($definition->ref);

            if ($docComment) {
                array_push($lines, $docComment, '');
            }

            //            if ($definition->properties->isNotEmpty()) {
            //                $lines[] = '#### Properties';
            //                $lines[] = '';
            //                $lines[] = '| Name | Type |';
            //                $lines[] = '| ---- | ---- |';
            //
            //                foreach ($definition->properties->all() as $property) {
            //                    $lines[] = sprintf(
            //                        "| {$property['name']} | %s |",
            //                        implode(', ', $property['types'] ?? [])
            //                    );
            //                }
            //
            //                $lines[] = '';
            //            }

            if ($definition->methods->isNotEmpty()) {
                $lines[] = '#### Methods';
                $lines[] = '';

                foreach ($definition->methods->all() as $method) {
                    $lines[] = "##### {$method->ref->getName()}";
                    $lines[] = '';

                    $parameters = $method->ref->getParameters();
                    $returnType = $method->ref->getReturnType();

                    $returnTypeName = $returnType ? $returnType->getName() : null;
                    $returnTypeFqcn = $returnTypeName && Str::contains($returnTypeName, '\\')
                        ? sprintf("\\%s", $returnTypeName)
                        : $returnTypeName;

                    $returnTypeString = $returnTypeFqcn ? sprintf(': %s', $returnTypeFqcn) : '';

                    $docComment = $this->getPlainTextFromDocComment($method->ref);

                    if ($docComment) {
                        array_push($lines, $docComment, '');
                    }

                    $lines[] = '```php';

                    if (count($parameters)) {
                        $lines[] = "public function {$method->ref->getName()}(";

                        foreach ($parameters as $parameter) {
                            $type = $parameter->getType();

                            if (! $type) {
                                continue;
                            }

                            $lines[] = sprintf("    %s\$%s,", $type->getName(), $parameter->getName());
                        }

                        $lines[] = trim(")$returnTypeString");
                    } else {
                        $lines[] = "public function {$method->ref->getName()}()$returnTypeString";
                    }

                    array_push($lines, '```', '');
                }
            }
        }

        $this->writeLines($handle, $lines);
        $this->close($handle, $filename);
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return string
     */
    protected function getInternalLink(ReflectionClass $ref): string
    {
        return sprintf('[%s](%s)', $ref->getShortName(), $this->createPathToClass($ref->getName()));
    }

    /**
     * @param  ReflectionClass|\ReflectionMethod|\ReflectionParameter|\ReflectionProperty $ref
     * @param  null|int                                                                   $lineStart
     * @param  null|int                                                                   $lineEnd
     *
     * @return \ReflectionClass
     */
    protected function getLinkToSource($ref, ?int $lineStart = null, ?int $lineEnd = null): ?string
    {
        $filename = $ref->getFileName();

        if (! $filename) {
            return 'https://www.php.net/manual/en/index.php';
        }

        $baseUrl = sprintf(
            'https://github.com/myparcelnl/pdk/blob/%s/src/%s',
            self::BRANCH,
            Str::after($filename, 'src/')
        );

        $firstLine = $lineStart ?? $ref->getStartLine();
        $lastLine  = $lineEnd ?? $ref->getEndLine();

        if ($firstLine !== $lastLine) {
            $baseUrl .= sprintf('#L%d-L%d', $firstLine, $lastLine);
        } else {
            $baseUrl .= sprintf('#L%d', $firstLine);
        }

        return $baseUrl;
    }

    /**
     * @param  string $className
     *
     * @return string
     */
    private function createPathToClass(string $className): string
    {
        $parts      = explode('\\', $className);
        $kebabParts = array_map([Str::class, 'kebab'], array_slice($parts, 2));

        return implode('/', $kebabParts);
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return array
     */
    private function getRequired(ClassDefinition $definition): array
    {
        /** @var \MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue $required */
        $required = $definition->comments->firstWhere('key', 'pdk-required');

        if (! $required) {
            return ['', ''];
        }

        $icon = $this->getRequiredIcon($required);
        $text = $required->value;

        if (in_array($required->value, ['true', 'false'])) {
            $text = '';
        }

        return [$icon, $text];
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue $required
     *
     * @return string
     */
    private function getRequiredIcon(KeyValue $required): string
    {
        switch ($required->value) {
            case 'true':
                return '🔴';
            case 'false':
                return '💚';
            default:
                return '🟨';
        }
    }
}
