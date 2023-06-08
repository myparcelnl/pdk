<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use Throwable;

abstract class AbstractPdkAccountRepository extends Repository implements PdkAccountRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepository
     */
    private $accountRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface    $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository $accountRepository
     */
    public function __construct(StorageInterface $storage, AccountRepository $accountRepository)
    {
        parent::__construct($storage);
        $this->accountRepository = $accountRepository;
    }

    /**
     * Get the saved account.
     */
    abstract protected function getFromStorage(): ?Account;

    /**
     * Store the given account. If null is given, the account data should be deleted.
     */
    abstract protected function store(?Account $account): ?Account;

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

                $this->markApiKeyAsValid($apiKey);

                return $account;
            } catch (Throwable $e) {
                $this->markApiKeyAsInvalid($apiKey);
                throw $e;
            }
        });
    }

    /**
     * @return string
     */
    protected function getInvalidKeyName(): string
    {
        return 'invalid_api_key';
    }

    /**
     * @param  null|string $apiKey
     *
     * @return bool
     */
    protected function isInvalidApiKey(?string $apiKey): bool
    {
        return ! $apiKey || $this->retrieve($this->getInvalidKeyName()) === $apiKey;
    }

    /**
     * @param  string $apiKey
     *
     * @return void
     */
    protected function markApiKeyAsInvalid(string $apiKey): void
    {
        $this->save($this->getInvalidKeyName(), $apiKey);
    }

    /**
     * @return void
     */
    protected function markApiKeyAsValid(): void
    {
        $this->storage->delete($this->getKeyPrefix() . $this->getInvalidKeyName());
    }
}
