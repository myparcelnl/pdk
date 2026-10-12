<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PickupAnyOfLocation;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('limits fulfilment customs descriptions without changing stored data or product names', function () {
    $description = str_repeat('é', 51);
    $recipient   = new ContactDetails(['cc' => CountryCodes::CC_US]);
    $shipment    = new Shipment([
        'carrier'            => factory(Carrier::class)
            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
            ->make(),
        'recipient'          => $recipient,
        'customsDeclaration' => new CustomsDeclaration([
            'invoice' => '1234',
            'items'   => [[
                'description' => $description,
                'weight'      => 100,
                'itemValue'   => ['amount' => 1000, 'currency' => 'EUR'],
            ]],
        ]),
    ]);
    $order = new Order([
        'externalIdentifier' => '1234',
        'invoiceAddress'     => $recipient,
        'shipment'           => $shipment,
        'lines'              => [['product' => ['name' => $description]]],
    ]);
    $originalDeclaration = $shipment->customsDeclaration->toStorableArray();

    $request = new PostOrdersRequest(new OrderCollection([$order]));
    $body    = json_decode($request->getBody(), true);
    $encoded = $body['data']['orders'][0];

    expect($encoded['shipment']['customs_declaration']['items'][0]['description'])
        ->toBe(str_repeat('é', 47) . '...')
        ->and($encoded['order_lines'][0]['product']['name'])->toBe($description)
        ->and($shipment->customsDeclaration->toStorableArray())->toBe($originalDeclaration);
});

it('sends the external product reference only for fulfilment customs items that have one', function () {
    $recipient = new ContactDetails(['cc' => CountryCodes::CC_US]);
    $order     = new Order([
        'externalIdentifier' => '1234',
        'invoiceAddress'     => $recipient,
        'shipment'           => new Shipment([
            'carrier'            => factory(Carrier::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                ->make(),
            'recipient'          => $recipient,
            'customsDeclaration' => new CustomsDeclaration([
                'invoice' => '1234',
                'items'   => [
                    [
                        'description'              => 'With reference',
                        'externalProductReference' => 'catalog_item_abc123def456g',
                        'weight'                   => 100,
                        'itemValue'                => ['amount' => 1000, 'currency' => 'EUR'],
                    ],
                    [
                        'description' => 'Without reference',
                        'weight'      => 100,
                        'itemValue'   => ['amount' => 1000, 'currency' => 'EUR'],
                    ],
                ],
            ]),
        ]),
    ]);

    $body  = json_decode((new PostOrdersRequest(new OrderCollection([$order])))->getBody(), true);
    $items = $body['data']['orders'][0]['shipment']['customs_declaration']['items'];

    expect($items[0]['external_product_reference'])
        ->toBe('catalog_item_abc123def456g')
        ->and($items[1])
        ->not->toHaveKey('external_product_reference');
});

it('sends no pickup location type to the fulfilment api', function (?string $type) {
    $location = factory(RetailLocation::class)
        ->inTheNetherlands()
        ->withType($type)
        ->make();
    $shipment = new Shipment([
        'carrier'      => factory(Carrier::class)
            ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
            ->make(),
        'recipient'    => new ContactDetails(['cc' => CountryCodes::CC_NL]),
        'pickup'       => $location,
        'dropOffPoint' => $location,
    ]);
    $order = new Order([
        'externalIdentifier' => '1234',
        'invoiceAddress'     => new ContactDetails(['cc' => CountryCodes::CC_NL]),
        'shipment'           => $shipment,
    ]);

    $body    = json_decode((new PostOrdersRequest(new OrderCollection([$order])))->getBody(), true);
    $encoded = $body['data']['orders'][0]['shipment'];

    expect($encoded['pickup'])
        ->toHaveKey('location_code', '215795')
        ->not->toHaveKey('type')
        ->not->toHaveKey('location_type')
        ->and($encoded['drop_off_point'])
        ->toHaveKey('location_code', '215795')
        ->not->toHaveKey('type')
        ->not->toHaveKey('location_type');
})->with([
    'parcel locker' => [PickupAnyOfLocation::TYPE_PARCEL_LOCKER],
    'no type'       => [null],
]);

it('sends no empty pickup location fields to the fulfilment api', function () {
    $location = factory(RetailLocation::class)
        ->fromScratch()
        ->withLocationCode('215795')
        ->withCc(CountryCodes::CC_NL)
        ->withType(PickupAnyOfLocation::TYPE_PARCEL_LOCKER)
        ->make();
    $order    = new Order([
        'externalIdentifier' => '1234',
        'invoiceAddress'     => new ContactDetails(['cc' => CountryCodes::CC_NL]),
        'shipment'           => new Shipment([
            'carrier'      => factory(Carrier::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                ->make(),
            'recipient'    => new ContactDetails(['cc' => CountryCodes::CC_NL]),
            'pickup'       => $location,
            'dropOffPoint' => $location,
        ]),
    ]);

    $body    = json_decode((new PostOrdersRequest(new OrderCollection([$order])))->getBody(), true);
    $encoded = $body['data']['orders'][0]['shipment'];

    expect($encoded['pickup'])
        ->toBe(['location_code' => '215795', 'cc' => CountryCodes::CC_NL])
        ->and($encoded['drop_off_point'])
        ->toBe(['location_code' => '215795', 'cc' => CountryCodes::CC_NL]);
});
