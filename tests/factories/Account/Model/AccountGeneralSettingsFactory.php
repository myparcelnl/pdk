<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of AccountGeneralSettings
 * @method AccountGeneralSettings make()
 * @method $this withHasCarrierContract(bool $hasCarrierContract)
 * @method $this withHasCarrierSmallPackageContract(bool $hasCarrierSmallPackageContract)
 * @method $this withIsTest(bool $isTest)
 * @method $this withOrderMode(bool $orderMode)
 */
final class AccountGeneralSettingsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return AccountGeneralSettings::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\AccountGeneralSettings $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $accountRepository */
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
        $existingAccount   = $accountRepository->getAccount();

        if ($existingAccount) {
            $existingAccount->generalSettings = $model;
            $accountRepository->store($existingAccount);
            return;
        }

        factory(Account::class)
            ->withGeneralSettings($model)
            ->store();
    }
}
