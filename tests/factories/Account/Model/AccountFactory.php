<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\ContactDetailsFactory;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Concern\HasIncrementingId;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Account
 * @method Account make()
 * @method $this withContactInfo(ContactDetails|ContactDetailsFactory $contactInfo)
 * @method $this withGeneralSettings(AccountGeneralSettings|AccountGeneralSettingsFactory $generalSettings)
 * @method $this withId(int $id)
 * @method $this withPlatformId(int $platformId)
 * @method $this withStatus(int $status)
 */
final class AccountFactory extends AbstractModelFactory
{
    use HasIncrementingId;

    public function getModel(): string
    {
        return Account::class;
    }

    public function onPlatformFlespakket(): self
    {
        return $this->withPlatformId(Platform::FLESPAKKET_ID);
    }

    public function onPlatformMyParcel(): self
    {
        return $this->withPlatformId(Platform::MYPARCEL_ID);
    }

    public function onPlatformSendMyParcel(): self
    {
        return $this->withPlatformId(Platform::SENDMYPARCEL_ID);
    }

    /**
     * @param  int|ShopCollection|CollectionFactoryInterface|ModelFactoryInterface[] $shops
     *
     * @return $this
     */
    public function withShops($shops = 1): ModelFactoryInterface
    {
        return $this->withCollection('shops', $shops, function (ShopFactory $factory) {
            return $factory
                ->withPlatformId($this->attributes->get('platformId'))
                ->withAccountId($this->attributes->get('id'))
                ->withDeliveryAddress($this->attributes->get('contactInfo'));
        });
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withId($this->getNextId())
            ->onPlatformMyParcel()
            ->withStatus(2)
            ->withContactInfo(factory(ContactDetails::class))
            ->withGeneralSettings(factory(AccountGeneralSettings::class))
            ->withShops(factory(ShopCollection::class, 1));
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Account $model
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\NotImplementedException
     */
    protected function save(Model $model): void
    {
        /** @var PdkAccountRepositoryInterface $accountRepository */
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);

        $accountRepository->store($model);
    }
}
