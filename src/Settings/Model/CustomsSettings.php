<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect;

/**
 * @property string $from
 * @property string $code
 * @property string $origin
 */
class CustomsSettings extends Model
{
    protected $attributes = [
        'defaultForm'          => null,
        'defaultCustomsCode'   => null,
        'defaultCountryOrigin' => CountrySelect::class,
    ];

    protected $casts      = [
        'defaultForm'          => 'string',
        'defaultCustomsCode'   => 'string',
        'defaultCountryOrigin' => CountrySelect::class,
    ];
}
