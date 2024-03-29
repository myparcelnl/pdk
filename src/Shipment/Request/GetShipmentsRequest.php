<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShipmentsRequest extends Request
{
    /**
     * @var array
     */
    private $ids;

    /**
     * @param  array $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
        parent::__construct();
    }

    /**
     * @return string
     */
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
