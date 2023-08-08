<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property string|null $apiKey
 * @property bool        $apiKeyValid
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
    public const API_KEY       = 'apiKey';
    public const API_KEY_VALID = 'apiKeyValid';

    protected $attributes = [
        'id' => self::ID,

        self::API_KEY       => null,
        self::API_KEY_VALID => true,
    ];

    protected $casts      = [
        self::API_KEY       => 'string',
        self::API_KEY_VALID => 'bool',
    ];
}
