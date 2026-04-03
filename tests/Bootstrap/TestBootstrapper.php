<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
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

        Pdk::get(PropositionService::class)->clearActivePropositionId();

        $platformId = Proposition::MYPARCEL_ID;
        if (Proposition::SENDMYPARCEL_NAME === $platform) {
            $platformId = Proposition::SENDMYPARCEL_ID;
        }

        self::hasApiKey();

        factory(Account::class, $platformId)
            ->withShops()
            ->store();
    }

    /**
     * Set up test environment for a specific proposition using Proposition constants
     *
     * @param  int $propositionId Use Proposition::MYPARCEL_ID, Proposition::SENDMYPARCEL_ID, etc.
     * @return void
     */
    public static function forProposition(int $propositionId): void
    {
        MockPdkFactory::create();

        Pdk::get(PropositionService::class)->clearActivePropositionId();

        self::hasApiKey();

        factory(Account::class)
            ->forProposition($propositionId)
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

        factory(Account::class, Proposition::MYPARCEL_ID)
            ->withStatus(2)
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
