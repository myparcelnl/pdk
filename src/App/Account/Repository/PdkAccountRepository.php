<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\StorageRepository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use Throwable;

class PdkAccountRepository extends StorageRepository implements PdkAccountRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepository
     */
    private $accountRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface  $cache
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository    $accountRepository
     */
    public function __construct(
        CacheStorageInterface  $cache,
        StorageDriverInterface $storage,
        AccountRepository      $accountRepository
    ) {
        parent::__construct($cache, $storage);
        $this->accountRepository = $accountRepository;
    }

    /**
     * When the account is not found in the cache, nor the storage, try to get it from the API.
     *
     * @param  bool $force
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function get(bool $force = false): ?Account
    {
        return $this->retrieve($this->getIdentifier(), function () {
            $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

            if ($this->isInvalidApiKey($apiKey)) {
                return null;
            }

            try {
                $account = $this->accountRepository->getAccount();

                $this->markApiKeyAsValid();

                return $account;
            } catch (Throwable $e) {
                $this->markApiKeyAsInvalid($apiKey);
                throw $e;
            }
        }, $force);
    }

    public function getAccount(bool $force = false): ?Account
    {
        return $this->get($force);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $key
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function store(?Account $key): ?Account
    {
        if (! $key) {
            $this->delete($this->getIdentifier());
            return null;
        }

        return $this->save($this->getIdentifier(), $key);
    }

    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'account';
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
        $this->delete($this->getInvalidKeyName());
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function transformData(string $key, $data)
    {
        switch ($key) {
            case $this->getIdentifier():
                return Utils::cast(Account::class, $data);

            default:
                return parent::transformData($key, $data);
        }
    }
}
