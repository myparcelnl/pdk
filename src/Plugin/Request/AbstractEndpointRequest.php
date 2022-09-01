<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request;

use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Base\Support\Arrayable;

abstract class AbstractEndpointRequest extends Request implements Arrayable
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'body'       => $this->getBody(),
            'headers'    => $this->getHeaders(),
            'method'     => $this->getMethod(),
            'path'       => $this->getPath(),
            'parameters' => $this->getParameters(),
        ];
    }
}
