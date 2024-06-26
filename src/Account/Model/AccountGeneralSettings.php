<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool $isTest
 * @property bool $orderMode
 * @property bool $hasCarrierContract
 * @property bool $hasCarrierMailContract
 */
class AccountGeneralSettings extends Model
{
    public $attributes = [
        'isTest'                 => false,
        'orderMode'              => false,
        'hasCarrierContract'     => false,
        'hasCarrierMailContract' => false,
    ];

    public $casts      = [
        'isTest'                 => 'bool',
        'orderMode'              => 'bool',
        'hasCarrierContract'     => 'bool',
        'hasCarrierMailContract' => 'bool',
    ];
}
