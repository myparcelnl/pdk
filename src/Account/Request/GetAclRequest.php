<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetAclRequest extends Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/acl';
    }
}
