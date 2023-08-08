<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/carrier_management/shops/2100/carrier_options' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (): ClientResponseInterface {
        return new BehatClientResponse(
            [
                'data' => [
                    'carrier_options' => [
                        [
                            'id'              => 7,
                            'carrier'         => ['id' => Carrier::CARRIER_POSTNL_ID],
                            'enabled'         => true,
                            'optional'        => false,
                            'subscription_id' => null,
                            'type'            => 'main',
                        ],
                        [
                            'id'              => 8,
                            'carrier'         => ['id' => Carrier::CARRIER_DHL_FOR_YOU_ID],
                            'enabled'         => true,
                            'optional'        => true,
                            'subscription_id' => 8123,
                            'type'            => 'custom',
                        ],
                    ],
                ],
            ],
            200
        );
    },
];
