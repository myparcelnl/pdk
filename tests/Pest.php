<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Facade;

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

uses()
    ->afterEach(function () {
        // Reset Facade after each test.
        Facade::setPdkInstance(null);
    })
    ->in(__DIR__);

uses()
    ->group('model')
    ->in(__DIR__ . '/Unit/Base/Model');
