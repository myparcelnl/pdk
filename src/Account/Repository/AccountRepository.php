<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetAccountsRequest;
use MyParcelNL\Pdk\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;

class AccountRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccount(): Account
    {
        return $this->retrieve('account', function () {
            /** @var GetAccountsResponse $response */
            $response = $this->api->doRequest(new GetAccountsRequest(), GetAccountsResponse::class);

            return $response->getAccount();
        });
    }
}
