<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShipmentsRequest extends Request
{
    /**
     * @var int[]|string[]
     */
    private $ids;

    /**
     * @param  int[]|string[] $ids
     * @param  array          $config
     */
    public function __construct(array $ids = [], array $config = [])
    {
        foreach ($ids as $id) {
            if (! is_scalar($id)) {
                throw new \InvalidArgumentException('GetShipmentsRequest expects an array of scalar IDs');
            }
        }

        $this->ids = $ids;
        parent::__construct($config);
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
