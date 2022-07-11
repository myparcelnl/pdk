<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Factory\ShipmentValidatorFactory;
use MyParcelNL\Pdk\Shipment\Validator\PostNLShipmentValidator;

it('gets the validator for a carrier', function () {
    $validator = ShipmentValidatorFactory::create(1);

    expect($validator)->toBeInstanceOf(PostNLShipmentValidator::class);
});
