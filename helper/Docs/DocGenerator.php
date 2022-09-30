<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Docs;

use MyParcelNL\Pdk\Helper\Php\PhpHelperGenerator;
use MyParcelNL\Pdk\Helper\Shared\PhpDoc;
use MyParcelNL\Sdk\src\Support\Arr;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

class DocGenerator extends PhpHelperGenerator
{
    /**
     * @var string[]
     */
    private $fileLinks;

    /**
     * @param  \ReflectionClass   $currentClass
     * @param  ReflectionMethod[] $reflectionMethods
     *
     * @return string
     */
    public function getInheritedMethodsList(ReflectionClass $currentClass, array $reflectionMethods): string
    {
        $inheritedMethods = [];

        foreach ($reflectionMethods as $method) {
            $declaringClass = $method->getDeclaringClass();
            // Ignore own methods
            if ($declaringClass->isUserDefined()
                || $declaringClass
                    ->getName() === $currentClass->getName()
            ) {
                continue;
            }

            // get description from reflection method doc comment
            $inheritedMethods[] =
                sprintf(
                    '- %s',
                    $this->getLinkToClass(
                        $declaringClass
                            ->getName(),
                        $declaringClass->getName() . '::' . $method->getName(),
                        true

                    )
                );
        }

        return implode(PHP_EOL, $inheritedMethods);
    }

    /**
     * @param  \ReflectionMethod $method
     *
     * @return string[]
     * @throws \ReflectionException
     */
    public function getMethodParameters(ReflectionMethod $method): array
    {
        return array_map(
            function (ReflectionParameter $parameter) {
                $parameterString = '';

                if ($parameter->hasType() && $parameter->getType()) {
                    if ($parameter->getType()
                        ->allowsNull()) {
                        $parameterString .= '?';
                    }

                    $parameterString .= sprintf(
                        '%s ',
                        /*$this->getLinkToClass(*/
                        $parameter->getType()
                            ->getName()
                    //                        )
                    );
                }

                if ($parameter->isPassedByReference()) {
                    $parameterString .= '&';
                }

                if ($parameter->isVariadic()) {
                    $parameterString .= '...';
                }

                $parameterString .= "\${$parameter->getName()}";

                if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
                    $defaultValue = $parameter->getDefaultValue();

                    if (is_array($defaultValue)) {
                        $defaultValue = '[]';
                    } elseif (is_string($defaultValue)) {
                        $defaultValue = "'$defaultValue'";
                    } elseif (is_bool($defaultValue)) {
                        $defaultValue = $defaultValue ? 'true' : 'false';
                    } elseif (null === $defaultValue) {
                        $defaultValue = 'null';
                    }

                    $parameterString .= ' = ' . $defaultValue;
                }

                return $parameterString;
            },
            $method->getParameters()
        );
    }

    /**
     * @param  \ReflectionClass   $currentClass
     * @param  ReflectionMethod[] $reflectionMethods
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getMethodsList(ReflectionClass $currentClass, array $reflectionMethods): string
    {
        $methodsList = '';

        foreach ($reflectionMethods as $method) {
            // Ignore inherited methods
            if ($method->isInternal()
                || $method->isConstructor()
                || $method->getDeclaringClass()
                    ->getName() !== $currentClass->getName()) {
                continue;
            }

            $name         = $method->getName();
            $parentClass  = $currentClass->getParentClass();
            $parentMethod = $parentClass && $parentClass->hasMethod($name) ? $parentClass->getMethod($name) : null;

            $parameters = $this->getMethodParameters($method);

            if ($parentMethod) {
                $parentLink = $this->getLinkToClass(
                    $parentClass->getName(),
                    '🔺',
                    true
                );
            }

            $modifiers = implode(
                ' ',
                array_merge(Reflection::getModifierNames($method->getModifiers()), ['function'])
            );

            //            "\n  " . implode("`,\n  `$", $parameters) . "\n"
            //            $methodStr = sprintf(
            //                '%s %s(%s): %s',
            //                $modifiers,
            //                $name,
            //                count($parameters) ? implode('`, `$', $parameters) : '',
            //                $method->getReturnType() ? $this->getLinkToClass(
            //                    $method->getReturnType()
            //                        ->getName()
            //                ) : 'void'
            //            );
            $methodStr = sprintf(
                "```php\n%s %s(%s): %s\n```",
                $modifiers,
                $name,
                count($parameters) ? implode(', ', $parameters) : '',
                $method->getReturnType() ? $method->getReturnType()
                    ->getName() : 'void'
            );

            $docComment = $method->getDocComment();

            $parentDocComment = $parentMethod ? $parentMethod->getDocComment() : '';

            $returnType = $method->getReturnType() ?
                $this->getLinkToClass(
                    $method->getReturnType()
                        ->getName()
                )
                : 'void';

            //            $parameters      = $this->getMethodParameters($method);

            $doc       = new PhpDoc($method);
            $parentDoc = $parentMethod ? new PhpDoc($parentMethod) : null;

            $parsedDoc       = $this->parseDocComment($method);
            $parsedParentDoc = $parentMethod ? $this->parseDocComment($parentMethod) : null;

            $parameterTable = $method->getNumberOfParameters() ? $this->toMarkdownTable(['Name', 'Type', 'Description'],
                array_map(function (ReflectionParameter $parameter) use ($parsedDoc) {
                    return [
                        $parameter->getName(),
                        $parameter->getType() ? $this->getLinkToClass(
                            $parameter->getType()
                                ->getName()
                        ) : 'mixed',
                        implode(
                            ', ',
                            Arr::first($parsedDoc, static function (array $item) use ($parameter) {
                                return 'param' === $item['param'] && $item['name'] === $parameter->getName();
                            })['types'] ?? []
                        ),
                    ];
                }, $method->getParameters())) : null;

            $parameterList = $method->getNumberOfParameters() ? [
                '**Parameters**',
                $this->renderList(
                    array_map(function (ReflectionParameter $parameter) {
                        $parameterType = $parameter->getType()
                            ? $this->getLinkToClass(
                                $parameter->getType()
                                    ->getName()
                            ) : '`mixed`';
                        return "`\${$parameter->getName()}`: $parameterType";
                    }, $method->getParameters())
                ),
            ] : [];

            $sourceLink = sprintf(
                'https://github.com/myparcelnl/pdk/blob/main/src/%s.php#L%s-L%s',
                strtr($currentClass->getName(), [
                    'MyParcelNL\\Pdk\\' => '',
                    '\\'                => '/',
                ]),
                $method->getStartLine(),
                $method->getEndLine()
            );

            $methodsList .= implode(
                    str_repeat(PHP_EOL, 2),
                    array_filter(
                        [
                            PHP_EOL . "##### $name",
                            '**Source:** ' . $this->createLink(
                                $currentClass->getName() . '::' . $method->getName(),
                                $sourceLink
                            ),
                            $parsedDoc['description'] ?? $parsedParentDoc['description'] ?? '',
                            '**Parameters**',
                            $parameterTable,
                            '**Return type:** ' . $returnType,
                            '**Implementation**',
                            $methodStr,
                        ]
                    )
                ) . PHP_EOL;
        }

        return $methodsList;
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return resource
     */
    protected function createHandle(ReflectionClass $ref)
    {
        $filename = explode('/src/', $ref->getFileName())[1];
        $filename = str_replace('.php', '', $filename);
        $filename = strtr($this->getFileName(), [':file' => $filename]);

        $directory = dirname($filename);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        return fopen($filename, 'wb+');
    }

    /**
     * @param  string $text
     * @param  string $link
     *
     * @return string
     */
    protected function createLink(string $text, string $link): string
    {
        $linkString = "[$text]";
        $fileLink   = "$linkString: $link";

        if (! in_array($fileLink, $this->fileLinks, true)) {
            $this->fileLinks[] = $fileLink;
        }

        return $linkString;
    }

    /**
     * @return string
     */
    protected function getFileName(): string
    {
        return BASE_DIR . '/types/docs/:file.md';
    }

    /**
     * @param  string      $type
     * @param  null|string $text
     * @param  bool        $inline
     *
     * @return string
     */
    protected function getLinkToClass(string $type, ?string $text = null, ?bool $inline = false): string
    {
        $linkText = $text ?? $type;

        if (class_exists($type)) {
            $parts = explode('\\', ltrim($type, '\\'));
            $path  = implode('/', array_slice($parts, 2));

            $linkText = $text ?? Arr::last($parts);

            if (! $inline) {
                return $this->createLink("`$linkText`", "$path.md");
            }

            return "[`$linkText`]($path.md)";
        }

        return "`$linkText`";
    }

    protected function getWhitelistClasses(): array
    {
        return [];
    }

    /**
     * @param  array $lines
     *
     * @return string
     */
    protected function renderList(array $lines): string
    {
        return implode(
            PHP_EOL,
            array_map(
                static function ($line) {
                    return "- $line";
                },
                $lines
            )
        );
    }

    /**
     * Renders a Markdown table from given header and data. "$header" is an array of strings, "$data" is an array of
     * arrays of strings. Each column should be as wide as the widest cell in that column. The header should be
     * rendered first, then all rows.
     *
     * @param  string[] $header
     * @param  array[]  $data
     *
     * @return string
     */
    protected function toMarkdownTable(array $header, array $data): string
    {
        $columnWidths = [];

        foreach (array_merge([$header], $data) as $row) {
            foreach ($row as $column => $value) {
                $columnWidths[$column] = max($columnWidths[$column] ?? 0, strlen($value));
            }
        }

        $lines = [];

        $headerLine = '|';
        foreach ($header as $column => $value) {
            $headerLine .= sprintf(' %s |', str_pad($value, $columnWidths[$column]));
        }

        $lines[] = $headerLine;

        $separatorLine = '|';
        foreach ($columnWidths as $columnWidth) {
            $separatorLine .= sprintf('%s|', str_repeat('-', $columnWidth + 2));
        }

        $lines[] = $separatorLine;

        foreach ($data as $row) {
            $line = '|';

            foreach ($row as $column => $value) {
                $line .= sprintf(' %s |', str_pad($value, $columnWidths[$column]));
            }

            $lines[] = $line;
        }

        return implode("\r", $lines);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function write(): void
    {
        foreach ($this->data as $data) {
            $this->fileLinks = [];

            /** @var ReflectionClass $ref */
            $ref        = $data['reflectionClass'];
            $properties = $data['properties'];
            $parents    = $data['parents'];

            $handle = $this->createHandle($ref);

            $reflectionMethods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

            $propertiesList = $this->toMarkdownTable(['Name', 'Type'],
                array_map(function (array $property) {
                    $typesArray = array_map(function (string $type) {
                        return $this->getLinkToClass($type);
                    }, $property['types'] ?? []);

                    $types = implode(' ', $typesArray);

                    return [$property['name'], $types];
                },
                    array_filter($properties, static function (array $property) {
                        return 'property' === $property['param'];
                    })));

            $parent = $ref->getParentClass();

            $parsedDoc       = $this->parseDocComment($ref);
            $parsedParentDoc = $parent ? $this->parseDocComment($parent) : [];

            $description = array_filter(
                               array_merge($parsedDoc, $parsedParentDoc),
                               static function ($value) {
                                   return 'description' === ($value['param'] ?? null);
                               }
                           )[0]['description'] ?? null;

            $parentString = $parent ? sprintf("\n**🔺Extends %s**", $this->getLinkToClass($parent->getName())) : '';

            $propertiesString = count($properties) > 0 ? sprintf("\n#### Properties\n\n%s", $propertiesList) : '';

            $methodsList   = $this->getMethodsList($ref, $reflectionMethods);
            $methodsString = $methodsList ? sprintf("\n#### Methods\n\n%s", $methodsList) : '';

            $inheritedMethodsList   = $this->getInheritedMethodsList($ref, $reflectionMethods);
            $inheritedMethodsString = $inheritedMethodsList ? sprintf(
                "\n#### Inherited Methods\n\n%s",
                $inheritedMethodsList
            ) : '';

            sort($this->fileLinks);
            $linksString = count($this->fileLinks) > 0 ? sprintf("\n%s", implode("\n", $this->fileLinks)) : '';

            $content = implode(
                "\n",
                array_filter([
                    $description,
                    $parentString,
                    $propertiesString,
                    $methodsString,
                    $inheritedMethodsString,
                    $linksString,
                ])
            );

            fwrite(
                $handle,
                <<<EOF
---
title: {$ref->getShortName()}
contributors: false
editLink: false
---

### {$ref->getShortName()}

$content

EOF
            );
        }
    }
}
