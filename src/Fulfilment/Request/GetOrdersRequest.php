<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;

class GetOrdersRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/fulfilment/orders';

    /**
     * @var null|string
     */
    private $parameters;

    /**
     * @param  array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array
     */
    protected function getQueryParameters(): array
    {
        return array_filter($this->parameters);
    }
}
