<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk;

use DI\Definition\Helper\FactoryDefinitionHelper;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use function DI\factory;

if (! function_exists('MyParcelNL\Pdk\linkDeprecatedInterface')) {
    function linkDeprecatedInterface(string $deprecated, string $replacement): FactoryDefinitionHelper
    {
        return factory(function () use ($deprecated, $replacement) {
            Logger::reportDeprecatedInterface($deprecated, $replacement);

            return Pdk::get($replacement);
        });
    }
}
