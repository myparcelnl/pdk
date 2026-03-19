<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Proposition\Proposition;

dataset('platforms', function () {
    yield Proposition::MYPARCEL_NAME;
    yield Proposition::SENDMYPARCEL_NAME;
});

dataset('propositions', function () {
    yield Proposition::MYPARCEL_ID;
    yield Proposition::SENDMYPARCEL_ID;
});
