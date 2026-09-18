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
