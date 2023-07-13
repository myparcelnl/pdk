<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Acl;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetAclResponse extends ApiResponseWithBody
{
    /**
     * @var mixed
     */
    private $acl;

    /**
     * @return \MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAcl(): Account
    {
        return $this->acl;
    }

    protected function parseResponseBody(): void
    {
        $data = json_decode($this->getBody(), true);

        $this->acl = new Acl([]);
    }
}
