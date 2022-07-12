<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Account\Request\AbstractRequest;

class GetShipmentsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/shipments';

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

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function getQueryParameters(): array
    {
        return array_filter($this->parameters);
    }
}
