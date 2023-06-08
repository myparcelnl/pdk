<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;

interface PdkAccountRepositoryInterface
{
    /**
     * Get the account data from the storage or the api.
     *
     * @see \MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository
     */
    public function getAccount(bool $force = false): ?Account;
}
