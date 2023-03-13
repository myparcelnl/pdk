<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;

interface AccountRepositoryInterface
{
    /**
     * Retrieve the account belonging to the API key in Settings.
     */
    public function getAccount(bool $force = false): ?Account;

    /**
     * Store account in your platform. If account is null, delete its data.
     */
    public function store(?Account $account): ?Account;
}
