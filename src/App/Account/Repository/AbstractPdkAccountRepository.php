<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use Throwable;

/**
 * @deprecated Use \MyParcelNL\Pdk\Account\Repository\PdkAccountRepository instead. Will be removed in v3.0.0.
 * @see        \MyParcelNL\Pdk\App\Account\Repository\PdkAccountRepository
 */
abstract class AbstractPdkAccountRepository extends Repository implements
    PdkAccountRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepository
     */
    private $accountRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface           $cache
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository             $accountRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     */
    public function __construct(
        CacheStorageInterface          $cache,
        AccountRepository              $accountRepository,
        PdkSettingsRepositoryInterface $settingsRepository
    ) {
        Logger::reportDeprecatedClass(__CLASS__, PdkAccountRepository::class);
        parent::__construct($cache);
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
     *
     * @return bool
     */
    protected function isInvalidApiKey(?string $apiKey): bool
    {
        if (! $apiKey) {
            return true;
        }

        $accountSettings = $this->settingsRepository->all()->account;

        return $accountSettings->apiKey === $apiKey && ! $accountSettings->apiKeyValid;
    }

    /**
     * @param  bool $apiKeyIsValid
     *
     * @return void
     */
    protected function setApiKeyValidity(bool $apiKeyIsValid): void
    {
        $accountSettings = $this->settingsRepository->all()->account
            ->fill([
                AccountSettings::API_KEY_VALID => $apiKeyIsValid,
            ]);

        $this->settingsRepository->storeSettings($accountSettings);
    }
}
