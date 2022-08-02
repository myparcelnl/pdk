<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $apiKey
 * @property bool   $apiLogging
 * @property bool   $shareCustomerInformation
 * @property bool   $useSecondAddressField
 * @property bool   $conceptShipments
 * @property bool   $pps
 * @property string $priceType
 */
class GeneralSettings extends Model
{
    protected $attributes = [
        'apiKey'                   => null,
        'apiLogging'               => false,
        'shareCustomerInformation' => false,
        'useSecondAddressField'    => false,
        'conceptShipments'         => true,
        'pps'                      => false,
        'priceType'                => null,
    ];

    protected $casts      = [
        'apiKey'                   => 'string',
        'apiLogging'               => 'bool',
        'shareCustomerInformation' => 'bool',
        'useSecondAddressField'    => 'bool',
        'conceptShipments'         => 'bool',
        'pps'                      => 'bool',
        'priceType'                => 'string',
    ];
}
