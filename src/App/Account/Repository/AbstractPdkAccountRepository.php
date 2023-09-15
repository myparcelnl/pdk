<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use Throwable;

abstract class AbstractPdkAccountRepository extends Repository implements PdkAccountRepositoryInterface
{
    public function __construct(
        StorageInterface                             $storage,
        private readonly AccountRepository           $accountRepository,
        private readonly SettingsRepositoryInterface $settingsRepository
    ) {
        parent::__construct($storage);
    }

    /**
     * Get the saved account.
     */
    abstract protected function getFromStorage(): ?Account;

    public function getAccount(bool $force = false): ?Account
    {
        $account = $this->getFromStorage();

        if ($account) {
            $this->save('account', $account);
        }

        return $this->retrieve('account', function () {
            $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

            if ($this->isInvalidApiKey($apiKey)) {
                return null;
            }

            try {
                $account = $this->accountRepository->getAccount();

                $this->setApiKeyValidity(true);

                return $account;
            } catch (Throwable $e) {
                $this->setApiKeyValidity(false);
                throw $e;
            }
        }, $force);
    }

    /**
     * @param  null|string $apiKey
     */
    protected function isInvalidApiKey(?string $apiKey): bool
    {
        if (! $apiKey) {
            return true;
        }

        $accountSettings = $this->settingsRepository->all()->account;

        return $accountSettings->apiKey === $apiKey && ! $accountSettings->apiKeyValid;
    }

    protected function setApiKeyValidity(bool $apiKeyIsValid): void
    {
        $accountSettings = $this->settingsRepository->all()->account
            ->fill([
                AccountSettings::API_KEY_VALID => $apiKeyIsValid,
            ]);

        $this->settingsRepository->storeSettings($accountSettings);
    }
}
