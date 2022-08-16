<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

class GetLabelsRequest extends AbstractRequest
{
    private const PATH            = 'shipment_labels/:ids';
    private const PATH_V2         = 'v2/shipment_labels/:ids';
    private const LIMIT_TO_USE_V2 = 25;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  array                                                  $parameters
     */
    public function __construct(ShipmentCollection $collection, array $parameters)
    {
        $this->collection = $collection;
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
     * @return string
     */
    public function getPath(): string
    {
        $usesV2Endpoint = $this->hasBulkPrepare();
        $path           = $usesV2Endpoint ? self::PATH_V2 : self::PATH;
        $ids            = $this->collection
            ->pluck('id')
            ->toArray();

        return strtr($path, [':ids' => implode(';', $ids)]);
    }

    /**
     * @return array
     */
    protected function getQueryParameters(): array
    {
        $parameters              = $this->parameters;
        $parameters['positions'] = implode(';', $parameters['positions'] ?? []);
        return array_filter($parameters);
    }

    /**
     * @return bool
     */
    private function hasBulkPrepare(): bool
    {
        return $this->collection->count() >= self::LIMIT_TO_USE_V2;
    }
}

