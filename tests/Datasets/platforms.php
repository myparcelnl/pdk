<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Platform;

dataset('platforms', function () {
    yield Platform::MYPARCEL_NAME;
    yield Platform::SENDMYPARCEL_NAME;
});
