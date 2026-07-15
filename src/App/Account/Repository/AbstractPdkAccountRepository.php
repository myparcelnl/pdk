<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use Throwable;

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
            // Api key marked as invalid or not set means no account available.
            if (
                ! $this->settingsRepository->all()->account->apiKeyValid
                || ! $this->settingsRepository->all()->account->apiKey
            ) {
                return null;
            }

            try {
                return $this->accountRepository->getAccount();
            } catch (Throwable $e) {
                // Treat a failed account fetch as "no account" instead of letting the
                // exception bubble up. Rendering must never break on an api failure,
                // otherwise the settings page (including the api key form needed to
                // fix the problem) would not be shown at all.
                Logger::error('Failed to fetch account', ['exception' => $e]);

                Notifications::error(
                    'Failed to fetch account',
                    $e->getMessage(),
                    Notification::CATEGORY_API
                );

                return null;
            }
        }, $force);
    }
}
