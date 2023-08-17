<?php

/**
 * @see https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html
 */

namespace PHPSTORM_META {

    // DI
    override(\MyParcelNL\Pdk\Facade\Pdk::get(), map(['' => '@']));

    // Factories
    override(\MyParcelNL\Pdk\Tests\Factory\FactoryFactory::create(), map(['' => '@Factory']));
    override(\MyParcelNL\Pdk\Tests\factory(), map(['' => '@Factory']));
}
