<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Factory\ShipmentValidatorFactory;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

it('validates shipments', function ($carrierClass) {
    /** @var \MyParcelNL\Pdk\Shipment\Validator\AbstractShipmentValidator $instance */
    $shipment = new Shipment(['carrier' => $carrierClass]);

    $instance = ShipmentValidatorFactory::create($carrierClass);

    expect(function () use ($shipment, $instance) {
        $instance->validateAll($shipment);
    })->not->toThrow(
        Throwable::class
    )
        // Pest keeps reporting "This test did not perform any assertions" :/
        ->and(1)
        ->toBe(1);
})->with([
    CarrierBpost::NAME    => CarrierBpost::class,
    CarrierDPD::NAME      => CarrierDPD::class,
    CarrierInstabox::NAME => CarrierInstabox::class,
    CarrierPostNL::NAME   => CarrierPostNL::class,
]);
