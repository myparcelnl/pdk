<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Sdk\src\Model\Account\Account;

class GetAccountsResponseWithBody extends AbstractApiResponseWithBody
{
    /**
     * @var mixed
     */
    private $account;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    protected function parseResponseBody(string $body): void
    {
        $data          = json_decode($body, true)['data']['accounts'][0];
        $this->account = new Account($data);
    }
}
