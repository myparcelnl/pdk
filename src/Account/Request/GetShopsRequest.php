<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class GetShopsRequest extends Request
{
    public function getPath(): string
    {
        return '/shops';
    }
}
