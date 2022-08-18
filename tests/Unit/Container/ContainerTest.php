<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Container;

it('resolves classes', function () {
    class TestClass { }

    $container         = Container::getInstance();
    $testClassInstance = $container->get(TestClass::class);
    expect($testClassInstance)->toBeInstanceOf(TestClass::class);
});
