<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Sdk\src\Model\Account\Account;

class GetAccountsResponse extends ApiResponseWithBody
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

    protected function parseResponseBody(): void
    {
        $data          = json_decode($this->getBody(), true)['data']['accounts'][0];
        $this->account = new Account($data);
    }
}
