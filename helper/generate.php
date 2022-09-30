<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Helper\Docs\DocGenerator;
use MyParcelNL\Pdk\Helper\Php\PhpHelperGenerator;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Helper\TypeScript\TypescriptHelperGenerator;

const BASE_DIR = __DIR__ . '/..';

require_once BASE_DIR . '/vendor/autoload.php';

/** @var AbstractHelperGenerator[] $generators */
$generators = [
    new DocGenerator(),
    new PhpHelperGenerator(),
    new TypescriptHelperGenerator(),
];

foreach ($generators as $generator) {
    $generator->generate();
}
