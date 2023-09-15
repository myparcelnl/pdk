<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;

final class MockPdkAccountRepository extends AbstractPdkAccountRepository
{
    /**
     * @var \MyParcelNL\Pdk\Account\Model\Account|null
     */
    private $storedAccount;

    /**
     * @return void
     */
    public function deleteAccount(): void
    {
        $this->store(null);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function store(?Account $account): ?Account
    {
        $this->storedAccount = $account;

        return $this->save('account', $account);
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    protected function getFromStorage(): ?Account
    {
        return $this->storedAccount;
    }
}
