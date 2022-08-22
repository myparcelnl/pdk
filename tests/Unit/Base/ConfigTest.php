<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Config;
use function DI\autowire;

const TEMP_CONFIG_FILE_PATH = __DIR__ . '/../../../config/pest-temp-config.php';

beforeEach(function () {
    PdkFactory::create([ConfigInterface::class => autowire(\MyParcelNL\Pdk\Base\Config::class)]);
    file_put_contents(
        TEMP_CONFIG_FILE_PATH,
        <<<'EOF'
<?php

return [
    'value' => [
        'sub' => 2
    ]
];
EOF
    );
});

afterEach(function () {
    unlink(TEMP_CONFIG_FILE_PATH);
});

it('gets entire config file', function () {
    $value = Config::get('pest-temp-config');

    expect($value)->toBeArray();
});

it('gets key from config file', function () {
    $value = Config::get('pest-temp-config.value.sub');

    expect($value)->toEqual(2);
});

it('throws error if config file does not exist', function () {
    Config::get('randomConfigFileThatDoesNotExist.property');
})->throws(InvalidArgumentException::class);
