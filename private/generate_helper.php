<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Sdk\src\Support\Str;
use Nette\Loaders\RobotLoader;

const BASE_DIR = __DIR__ . '/..';

require_once BASE_DIR . '/vendor/autoload.php';

echo '    Generating helper for models...' . PHP_EOL;

$loader = new RobotLoader;
$loader->addDirectory(BASE_DIR . '/src');

// Scans directories for classes / interfaces / traits
$loader->rebuild();

// Returns array of class => filename pairs
$models = array_filter(array_keys($loader->getIndexedClasses()), function (string $class) {
    return get_parent_class($class) === Model::class;
});

$outputFile = BASE_DIR . '/pdk_ide_helper.php';
$helperFile = fopen($outputFile, 'wb+');

fwrite($helperFile, '<?php /** @noinspection ALL */' . str_repeat(PHP_EOL, 2));

/**
 * @param  string   $namespace
 * @param  string[] $types
 * @param  string[] $uses
 *
 * @return array
 */
function getFqcns(string $namespace, array $types, array $uses): array
{
    $newTypes = [];

    foreach ($types as $type) {
        if (in_array($type, ['array', 'string', 'bool', 'int', 'null']) || Str::startsWith($type, '\\')) {
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
 * @param $types
 *
 * @return string
 */
function getTypeHint($types): string
{
    $singleType = '';

    if (count($types) > 1) {
        $collectionTypes = array_filter($types, static function ($type) {
            return class_exists($type) && in_array(Collection::class, class_parents($type), true);
        });

        $nullTypes = array_filter($types, static function ($type) {
            return 'null' === $type;
        });

        if (! empty($collectionTypes)) {
            $singleType = "$collectionTypes[0] ";
        }

        if (! empty($nullTypes)) {
            $singleType = "?$singleType";
        }
    } else {
        $singleType = "$types[0] ";
    }
    return $singleType;
}

foreach ($models as $model) {
    $ref          = new ReflectionClass($model);
    $fileContents = file_get_contents($ref->getFileName());

    preg_match_all('/^use\s+(.+);$/m', $fileContents, $uses);

    $isClass = class_exists($model);

    if ($isClass) {
        $model = "\\$model";
    }

    $comment = $ref->getDocComment();

    if (! $comment) {
        continue;
    }

    $pattern = "#@([a-zA-Z]+)\s+([|\[\]\w\s\\\]*?)\s+(\\$\w+)#";
    preg_match_all($pattern, $comment, $matches);

    $properties = [];
    $getters    = [];
    $setters    = [];

    $i = 0;

    foreach ($matches[3] as $property) {
        $baseProperty = str_replace('$', '', $property);

        $types    = explode('|', $matches[2][$i]);
        $fqcns    = getFqcns($ref->getNamespaceName(), $types, $uses[1]);
        $typeHint = getTypeHint($fqcns);

        $fqcnString = implode('|', $fqcns);

        $getter = Str::camel('get_' . $baseProperty);
        $setter = Str::camel(sprintf('set_%s_attribute', $baseProperty));

        $getters[]    = "@method $fqcnString $getter()";
        $setters[]    = "@method $fqcnString $setter($typeHint$$baseProperty)";
        $properties[] = "@property $fqcnString $property";

        $i++;
    }

    $propertyList = ' * ' . implode(
            PHP_EOL . ' * ',
            array_merge($properties, $getters, $setters)
        );

    fwrite(
        $helperFile,
        <<<EOF

namespace {$ref->getNamespaceName()};

/**
$propertyList
 */
class {$ref->getShortName()} { }


EOF
    );
}

fclose($helperFile);

echo " ✅  Wrote to $outputFile" . PHP_EOL;
