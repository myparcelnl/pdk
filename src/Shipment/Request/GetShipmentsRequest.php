<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShipmentsRequest extends Request
{
    public function __construct(private readonly array $ids)
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return sprintf('/shipments/%s', implode(';', $this->ids));
    }

    /**
     * @return int[]
     */
    protected function getParameters(): array
    {
        return parent::getParameters() + [
                'link_consumer_portal' => 1,
            ];
    }
}
