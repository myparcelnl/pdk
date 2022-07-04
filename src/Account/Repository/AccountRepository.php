<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetAccountsRequest;
use MyParcelNL\Pdk\Account\Response\GetAccountsResponseWithBody;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;

class AccountRepository extends AbstractRepository
{
    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\Account
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function getAccount(): Account
    {
        return $this->retrieve('account', function () {
            /** @var GetAccountsResponseWithBody $response */
            $response = $this->api->doRequest(new GetAccountsRequest(), GetAccountsResponseWithBody::class);

            return $response->getAccount();
        });
    }
}
