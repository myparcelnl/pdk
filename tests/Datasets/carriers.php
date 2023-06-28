<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;

dataset('carrierNames', function () {
    foreach (array_keys(Carrier::CARRIER_NAME_ID_MAP) as $name) {
        yield [$name];
    }
});

dataset('carrierIds', function () {
    foreach (Carrier::CARRIER_NAME_ID_MAP as $id) {
        yield [$id];
    }
});
