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
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Account
 * @method Account make()
 * @method $this withContactInfo(array|ContactDetails|ContactDetailsFactory $contactInfo)
 * @method $this withGeneralSettings(array|AccountGeneralSettings|AccountGeneralSettingsFactory $generalSettings)
 * @method $this withId(int $id)
 * @method $this withPlatformId(int $platformId)
 * @method $this withStatus(int $status)
 * @method $this withSubscriptionFeatures(array|Collection $features)
 */
final class AccountFactory extends AbstractModelFactory
{
    public function __construct(?int $platformId = null)
    {
        parent::__construct();

        // make sure the platform id is set from the start, when supplied as argument to the factory
        if ($platformId) {
            $this->withPlatformId($platformId);
            $this->store();
        }
    }

    public function getModel(): string
    {
        return Account::class;
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
            ->withId($this->getNextId());
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Account $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var PdkAccountRepositoryInterface $accountRepository */
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);

        $accountRepository->store($model);
    }
}
