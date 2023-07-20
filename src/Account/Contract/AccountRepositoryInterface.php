<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;

/**
 * @deprecated Will be removed in v3.0.0. Use PdkAccountRepositoryInterface instead.
 */
interface AccountRepositoryInterface
{
    /**
     * Get the account data from the storage or the api.
     *
     * @see \MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository
     */
    public function get(bool $force = false): ?Account;

    /**
     * @deprecated Use get() instead. Will be removed in v3.0.0.
     */
    public function getAccount(bool $force = false): ?Account;
}
