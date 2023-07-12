<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class ExamplePostOrderNotesResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'order_notes' => [
                    [
                        'note' => 'This is a note',
                        'author' => 'customer',
                    ],
                ],
            ],
        ];
    }
}
