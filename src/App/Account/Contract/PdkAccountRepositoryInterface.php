<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;

interface PdkAccountRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the account data from the storage or the api.
     *
     * @see \MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository
     */
    public function getAccount(bool $force = false): ?Account;

    /**
     * Store the given account. If null is given, the account data should be deleted.
     */
    public function store(?Account $account): ?Account;
}
