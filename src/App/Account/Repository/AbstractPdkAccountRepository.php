<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

abstract class AbstractPdkAccountRepository extends Repository implements PdkAccountRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepository
     */
    private $accountRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository             $accountRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     */
    public function __construct(
        StorageInterface               $storage,
        AccountRepository              $accountRepository,
        PdkSettingsRepositoryInterface $settingsRepository
    ) {
        parent::__construct($storage);
        $this->accountRepository  = $accountRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Get the saved account.
     */
    abstract protected function getFromStorage(): ?Account;

    /**
     * @param  bool $force
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(bool $force = false): ?Account
    {
        $account = $this->getFromStorage();

        if ($account) {
            $this->save('account', $account);
        }

        return $this->retrieve('account', function () {
            if (! $this->settingsRepository->all()->account->apiKeyValid) {
                return null;
            }

            return $this->accountRepository->getAccount();
        }, $force);
    }
}
