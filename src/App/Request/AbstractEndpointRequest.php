<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Contract\Arrayable;

abstract class AbstractEndpointRequest extends Request implements Arrayable
{
    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        return [
            'body'             => $this->getBody(),
            'headers'          => $this->getHeaders(),
            'method'           => $this->getMethod(),
            'parameters'       => $this->getParameters(),
            'path'             => $this->getPath(),
            'property'         => $this->getProperty(),
            'responseProperty' => $this->getResponseProperty(),
        ];
    }
}
