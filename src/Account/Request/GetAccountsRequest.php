<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class GetAccountsRequest extends Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/accounts';
    }
}
