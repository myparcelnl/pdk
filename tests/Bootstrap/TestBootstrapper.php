<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use function MyParcelNL\Pdk\Tests\factory;

final class TestBootstrapper
{
    public const API_KEY_VALID = 'valid-api-key';

    /**
     * @param  int|ShopCollection|CollectionFactoryInterface|ModelFactoryInterface[] $shops
     */
    public static function hasAccount(string $apiKey = self::API_KEY_VALID, $shops = 1): void
    {
        self::hasApiKey($apiKey);

        factory(Account::class)
            ->withStatus(2)
            ->withPlatformId(Platform::FLESPAKKET_ID)
            ->withShops($shops)
            ->store();
    }

    public static function hasApiKey(string $apiKey = self::API_KEY_VALID): void
    {
        factory(AccountSettings::class)
            ->withApiKey($apiKey)
            ->store();
    }
}
