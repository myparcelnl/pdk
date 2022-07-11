<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Validator\BpostShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\DPDShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\InstaboxShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\PostNLShipmentValidator;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

return [
    CarrierPostNL::NAME => [
        'class'          => CarrierPostNL::class,
        'validator'      => PostNLShipmentValidator::class,
        'home_countries' => [CountryCodes::CC_NL],
        'delivery_types' => DeliveryOptions::DELIVERY_TYPES_NAMES,
    ],

    CarrierDPD::NAME => [
        'class'          => CarrierDPD::class,
        'validator'      => DPDShipmentValidator::class,
        'home_countries' => [CountryCodes::CC_NL],
        'delivery_types' => [
            DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
        ],
    ],

    CarrierInstabox::NAME => [
        'class'          => CarrierInstabox::class,
        'validator'      => InstaboxShipmentValidator::class,
        'home_countries' => [AbstractConsignment::CC_NL],
        'delivery_types' => [
            DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        ],
    ],

    CarrierBpost::NAME => [
        'class'          => CarrierBpost::class,
        'validator'      => BpostShipmentValidator::class,
        'home_countries' => [AbstractConsignment::CC_NL],
        'delivery_types' => [
            DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
        ],
    ],
];
