<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

dataset('carrierNames', function () {
    foreach (array_keys(ApiMapperService::forCarrier()->v2ToIdMap()) as $name) {
        yield [$name];
    }
});

dataset('carrierIds', function () {
    foreach (ApiMapperService::forCarrier()->v2ToIdMap() as $id) {
        yield [$id];
    }
});
