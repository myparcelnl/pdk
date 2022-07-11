<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Pdk\Shipment\Validator\PostNLShipmentValidator;

it('gets entire config file', function () {
    $value = Config::get('carriers');

    expect($value)->toBeArray();
});

it('gets key from config file', function () {
    $value = Config::get('carriers.postnl.validator');

    expect($value)->toEqual(PostNLShipmentValidator::class);
});

it('throws error if config file does not exist', function () {
    Config::get('randomConfigFileThatDoesNotExist.property');
})->throws(InvalidArgumentException::class);
