<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetAccountsRequest;
use MyParcelNL\Pdk\Account\Response\GetAccountsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;

class AccountRepository extends ApiRepository
{
    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\Account
     * @noinspection PhpUnused
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
