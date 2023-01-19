<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use Exception;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Request\GetAccountsRequest;
use MyParcelNL\Pdk\Account\Response\GetAccountsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Facade\DefaultLogger;

abstract class AbstractAccountRepository extends ApiRepository implements AccountRepositoryInterface
{
    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    abstract public function getFromStorage(): ?Account;

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return \MyParcelNL\Pdk\Account\Model\Account
     */
    abstract public function store(?Account $account): ?Account;

    /**
     * @param  bool $force
     *
     * @return \MyParcelNL\Pdk\Account\Model\Account
     * @noinspection PhpUnused
     */
    public function getAccount(bool $force = false): ?Account
    {
        if (! $force) {
            $existingAccount = $this->getFromStorage();

            if ($existingAccount) {
                $this->save('account', $existingAccount);
            }
        }

        return $this->retrieve('account', function () {
            try {
                /** @var GetAccountsResponse $response */
                $response = $this->api->doRequest(new GetAccountsRequest(), GetAccountsResponse::class);

                return $response->getAccount();
            } catch (Exception $e) {
                DefaultLogger::warning('Could not retrieve account', ['exception' => $e]);
                return null;
            }
        });
    }

    /**
     * @return void
     */
    public function storeAccount(): void
    {
        $account = $this->getAccount();

        $this->store($account);
    }
}
