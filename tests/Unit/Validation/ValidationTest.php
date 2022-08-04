<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use Exception;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Validator;
use RuntimeException;

it('validates a PDK order', function (array $input) {
    $pdkOrder       = new PdkOrder($input);

    // TODO: test schrijven die valideert of foute order error gooit
    expect((new Validator($pdkOrder))->validate())->toThrow(RuntimeException::class);
})->with([
    'correct order'            => [
        'input'  => [
            'externalIdentifier' => '1',
            'recipient'          => [
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'postalCode' => '2132JE',
                'street'     => 'Antareslaan 31',
                'number'     => '31',
                'person'     => 'Mike Parcel',
                'email'      => 'test@myparcel.nl',
                'phone'      => '0619438574',
            ],
            'sender'             => [
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'postalCode' => '2132JE',
                'street'     => 'Antareslaan 31',
                'number'     => '31',
                'person'     => 'Mike Parcel',
                'email'      => 'test@myparcel.nl',
                'phone'      => '0619438574',
            ],
            'deliveryOptions'    => [
                'carrier'         => 'postnl',
                'date'            => '2022-02-02',
                'deliveryType'    => 'standard',
                'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                'pickupLocation'  => null,
                'shipmentOptions' => [
                    'ageCheck'         => true,
                    'insurance'        => 100,
                    'labelDescription' => 'Pizzadoos',
                    'largeFormat'      => false,
                    'onlyRecipient'    => false,
                    'return'           => false,
                    'sameDayDelivery'  => true,
                    'signature'        => false,
                ],
            ],
            'shipments'          => null,
            'platform'           => 'myparcel',
        ],
    ],
]);

it('returns a scheme to validate', function (array $input, array $output) {
    $pdkOrder        = new PdkOrder($input);
    $validationArray = (new Validator($pdkOrder))->getAllowedOptions();

    expect($validationArray)->toBe($output);
})->with([
    'postnl order' => [
        'input'  => [
            'externalIdentifier' => '1',
            'recipient'          => [
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'postalCode' => '2132JE',
                'street'     => 'Antareslaan 31',
                'number'     => '31',
                'person'     => 'Mike Parcel',
                'email'      => 'test@myparcel.nl',
                'phone'      => '0619438574',
            ],
            'sender'             => [
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'postalCode' => '2132JE',
                'street'     => 'Antareslaan 31',
                'number'     => '31',
                'person'     => 'Mike Parcel',
                'email'      => 'test@myparcel.nl',
                'phone'      => '0619438574',
            ],
            'deliveryOptions'    => [
                'carrier'         => 'postnl',
                'date'            => '2022-02-02',
                'deliveryType'    => 'standard',
                'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                'pickupLocation'  => null,
                'shipmentOptions' => [
                    'ageCheck'         => true,
                    'insurance'        => 100,
                    'labelDescription' => 'Pizzadoos',
                    'largeFormat'      => false,
                    'onlyRecipient'    => false,
                    'return'           => false,
                    'sameDayDelivery'  => false,
                    'signature'        => false,
                ],
            ],
            'shipments'          => null,
            'platform'           => 'myparcel',
        ],
        'output' => [
        ],
    ],
]);

