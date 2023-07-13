<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Request\GetAclRequest;
use MyParcelNL\Pdk\Account\Response\GetAclResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;

class AclRepository extends ApiRepository
{
    /**
     * @return \MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAcl(): Account
    {
        return $this->retrieve('acl', function () {
            /** @var \MyParcelNL\Pdk\Account\Response\GetAclResponse $response */
            $response = $this->api->doRequest(new GetAclRequest(), GetAclResponse::class);

            return $response->getAcl();
        });
    }
}
