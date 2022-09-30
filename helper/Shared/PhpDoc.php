<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared;

use MyParcelNL\Pdk\Base\Support\Collection;

class PhpDoc
{
    /**
     * @var string|null
     */
    public $author;

    /**
     * @var string|null
     */
    public $class;

    /**
     * @var string|null
     */
    public $comment;

    /**
     * @var string|null
     */
    public $deprecated;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $implements;

    /**
     * @var string|null
     */
    public $internal;

    /**
     * @var string|null
     */
    public $license;

    /**
     * @var string|null
     */
    public $link;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $methods;

    /**
     * @var string|null
     */
    public $namespace;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $params;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $properties;

    /**
     * @var string|null
     */
    public $return;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $see;

    /**
     * @var string|null
     */
    public $since;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    public $throws;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var string|null
     */
    public $uses;

    /**
     * @var string|null
     */
    public $var;

    /**
     * @var string|null
     */
    public $version;

    /**
     * @var \ReflectionClass|\ReflectionMethod|\ReflectionProperty
     */
    private $reflection;

    /**
     * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty $reflection
     */
    public function __construct($reflection)
    {
        $this->reflection = $reflection;

        $this->implements = new Collection();
        $this->methods    = new Collection();
        $this->params     = new Collection();
        $this->properties = new Collection();
        $this->see        = new Collection();
        $this->throws     = new Collection();

        $this->parse();
    }

    protected function parse(): void
    {
        $comment = $this->reflection->getDocComment();

        if (! $comment) {
            return;
        }

        $uses     = [];
        $fileName = $this->reflection->getFileName();

        if ($fileName) {
            $fileContents = file_get_contents($fileName);

            preg_match_all('/^use\s+(.+);$/m', $fileContents, $uses);
        }

        $this->description = $this->extractDescriptionFromPhpDocComment($comment);

        preg_match_all('#@(\w+)(?:\s+(.+))?#', $comment, $matchingTags);

        $i = 0;

        $array = [];

        //        foreach (array_filter($matchingTags[0]) as $tag) {
        //            $type = $matchingTags[1][$i] ?? null;
        //
        //            if (in_array($type, ['param', 'type', 'property', 'var', 'return'])) {
        //                $value = $matchingTags[2][$i] ?? null;
        //                preg_match_all(
        //                    '/(?:(?P<type>[|\[\]<>{}:,\w\s\\\]*?)\s+)?(?P<property>\$\w+)(?:\s+(?P<description>.+))?/',
        //                    $value,
        //                    $matches
        //                );
        //
        //                $baseProperty = str_replace('$', '', $matches['property'][0] ?? '');
        //
        //                $fqClassNames = $this->getFullyQualifiedClassNames(
        //                    $this->reflection->getNamespaceName(),
        //                    explode('|', $matches['type'][0] ?? ''),
        //                    $uses[1]
        //                );
        //
        //                $array[] = [
        //                    'param'       => $type,
        //                    'name'        => $baseProperty,
        //                    'types'       => $fqClassNames,
        //                    'description' => $matches['description'][0] ?? null,
        //                ];
        //
        //                $i++;
        //                continue;
        //            }
        //
        //            $array[] = [
        //                'param'       => $type,
        //                'name'        => $type,
        //                'types'       => [],
        //                'description' => $matchingTags[2][$i] ?? null,
        //            ];
        //
        //            $i++;
        //        }

        $pattern = "#@([a-zA-Z]+)\s+(?:([|\[\]<>{}:,\w\s\\\]*?)\s+)?(\\$\w+)(?:\s+(.+))?#";
        preg_match_all($pattern, $comment, $matchingTags);
    }

    /**
     * @param  string $comment
     *
     * @return string
     */
    private function extractDescriptionFromPhpDocComment(string $comment): string
    {
        $descriptionLines = [];
        $lines            = explode("\n", $comment);

        foreach ($lines as $line) {
            $trimmedLine = trim($line, " \t/*");

            if (! $trimmedLine) {
                continue;
            }

            if (0 === strpos($trimmedLine, '@')) {
                $this->parseLine($trimmedLine);
                continue;
            }

            $descriptionLines[] = $trimmedLine;
        }

        return implode(' ', $descriptionLines);
    }

    private function parseLine(string $trimmedLine)
    {
        $match = [
            'param'    => 'params',
            'method'   => 'methods',
            'property' => 'properties',
        ];

        $type = preg_replace('/@(\w+)[\s\n]]/', '$1', $trimmedLine, -1);

        $type;
    }
}
