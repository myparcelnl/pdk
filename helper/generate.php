<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Helper\Documentation\DocumentationGenerator;
use MyParcelNL\Pdk\Helper\Php\PhpHelperGenerator;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Helper\Shared\Service\PhpSourceParser;
use MyParcelNL\Pdk\Helper\TypeScript\TypescriptHelperGenerator;

const BASE_DIR = __DIR__ . '/..';

require_once BASE_DIR . '/vendor/autoload.php';

// First argument is the type of helper to generate. If not set, all helpers will be generated.
$type = $argv[1] ?? null;

$generators = [
    'docs' => DocumentationGenerator::class,
    'php'  => PhpHelperGenerator::class,
    'ts'   => TypescriptHelperGenerator::class,
];

$helperParser = new PhpSourceParser();
$srcParser    = new PhpSourceParser();

$helperParser->parseDirectory(BASE_DIR . '/helper');
$srcParser->parseDirectory(BASE_DIR . '/src');

foreach ($generators as $id => $class) {
    if ($type && $type !== $id) {
        continue;
    }

    $definitions = $srcParser->getDefinitions();

    if ('php' === $id) {
        $definitions = $definitions->merge($helperParser->getDefinitions());
    }

    /** @var AbstractHelperGenerator $generator */
    $generator = new $class($definitions);
    $generator->run();
}
