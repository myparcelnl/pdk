<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property string|null $apiKey
 */
class AccountSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'account';
    /**
     * Settings in this category.
     */
    public const API_KEY = 'apiKey';

    protected $attributes = [
        'id' => self::ID,

        self::API_KEY => null,
    ];

    protected $casts      = [
        self::API_KEY => 'string',
    ];
}
