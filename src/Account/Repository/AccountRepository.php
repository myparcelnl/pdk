<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Request\GetAccountsRequest;
use MyParcelNL\Pdk\Account\Response\GetAccountsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;

class AccountRepository extends ApiRepository
{
    /**
     * @return \MyParcelNL\Pdk\Account\Model\Account
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
