<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

function createRowShipmentWithCustomsItems(array $descriptions): Shipment
{
    return new Shipment([
        'carrier'            => factory(Carrier::class)
            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
            ->make(),
        'recipient'          => new ContactDetails([
            'cc'         => CountryCodes::CC_US,
            'city'       => 'New York',
            'person'     => 'John Doe',
            'postalCode' => '10001',
            'street'     => 'Main Street 1',
        ]),
        'deliveryOptions'    => ['packageType' => 1],
        'customsDeclaration' => new CustomsDeclaration([
            'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
            'invoice'  => '1234',
            'items'    => array_map(static function (?string $description) {
                return [
                    'amount'         => 1,
                    'classification' => '0000',
                    'country'        => CountryCodes::CC_NL,
                    'description'    => $description,
                    'itemValue'      => ['amount' => 1000, 'currency' => 'EUR'],
                    'weight'         => 100,
                ];
            }, $descriptions),
        ]),
    ]);
}

it('limits customs declaration item descriptions to the api maximum length', function () {
    $shortDescription   = 'Stofzuiger';
    $exactDescription   = str_repeat('a', 50);
    $asciiOverflow      = str_repeat('a', 51);
    $longDescription    = 'Stofzuiger met éxtra lange productnaam die de limiet van vijftig tekens overschrijdt';
    $multibyteOverflow  = str_repeat('é', 49) . 'ëx';

    $shipment = createRowShipmentWithCustomsItems([
        $shortDescription,
        $exactDescription,
        $asciiOverflow,
        $longDescription,
        $multibyteOverflow,
    ]);
    $originalDeclaration = $shipment->customsDeclaration->toStorableArray();

    $request = new PostShipmentsRequest(
        new ShipmentCollection([$shipment])
    );

    $body         = json_decode($request->getBody(), true);
    $descriptions = array_column($body['data']['shipments'][0]['customs_declaration']['items'], 'description');

    expect($descriptions)->toBe([
        $shortDescription,
        $exactDescription,
        str_repeat('a', 47) . '...',
        'Stofzuiger met éxtra lange productnaam die de l...',
        str_repeat('é', 47) . '...',
    ]);

    foreach ($descriptions as $description) {
        expect(mb_strlen($description, 'UTF-8'))->toBeLessThanOrEqual(50)
            ->and(mb_check_encoding($description, 'UTF-8'))->toBeTrue();
    }

    expect($shipment->customsDeclaration->toStorableArray())->toBe($originalDeclaration);
});

it('preserves a missing customs description while limiting other items', function () {
    $shipment = createRowShipmentWithCustomsItems([null, str_repeat('a', 51)]);
    $request  = new PostShipmentsRequest(new ShipmentCollection([$shipment]));
    $body     = json_decode($request->getBody(), true);
    $items    = $body['data']['shipments'][0]['customs_declaration']['items'];

    expect($items[0]['description'] ?? null)->toBeNull()
        ->and($items[1]['description'])->toBe(str_repeat('a', 47) . '...');
});
