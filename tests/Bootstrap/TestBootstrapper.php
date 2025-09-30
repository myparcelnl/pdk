<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use function MyParcelNL\Pdk\Tests\factory;

final class TestBootstrapper
{
    public const API_KEY_VALID = 'valid-api-key';

    public static function forPlatform(string $platform): void
    {
        MockPdkFactory::create();

        $platformId = Platform::MYPARCEL_ID;
        if (Platform::SENDMYPARCEL_NAME === $platform) {
            $platformId = Platform::SENDMYPARCEL_ID;
        }

        self::hasApiKey();

        factory(Account::class, $platformId)
            ->withShops()
            ->store();
    }

    /**
     * @param  string                                                                $apiKey
     * @param  int|ShopCollection|CollectionFactoryInterface|ModelFactoryInterface[] $shops
     *
     * @return void
     */
    public static function hasAccount(string $apiKey = self::API_KEY_VALID, $shops = 1): void
    {
        self::hasApiKey($apiKey);

        factory(Account::class)
            ->withStatus(2)
            ->withPlatformId(Platform::MYPARCEL_ID)
            ->withContactInfo(factory(ContactDetails::class))
            ->withGeneralSettings(factory(AccountGeneralSettings::class))
            ->withShops($shops)
            ->store();
    }

    /**
     * @param  string|null $apiKey
     *
     * @return void
     */
    public static function hasApiKey(?string $apiKey = self::API_KEY_VALID): void
    {
        factory(AccountSettings::class)
            ->withApiKey($apiKey)
            ->store();
    }

    public static function hasShippingMethods(): void
    {
        factory(PdkShippingMethod::class)
            ->withId('shipping:1')
            ->withName('Shipping 1')
            ->withIsEnabled(true)
            ->store();

        factory(PdkShippingMethod::class)
            ->withId('shipping:2')
            ->withName('Shipping 2')
            ->withIsEnabled(false)
            ->store();

        factory(PdkShippingMethod::class)
            ->withId('shipping:3')
            ->withName('Shipping 3')
            ->withDescription('My description')
            ->withIsEnabled(true)
            ->store();
    }
}
