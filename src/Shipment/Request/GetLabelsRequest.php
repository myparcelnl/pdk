<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

class GetLabelsRequest extends Request
{
    private const PATH            = 'shipment_labels/:ids';
    private const PATH_V2         = 'v2/shipment_labels/:ids';
    private const LIMIT_TO_USE_V2 = 25;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipmentCollection
     * @param  array                                                  $parameters
     */
    public function __construct(ShipmentCollection $shipmentCollection, array $parameters)
    {
        $this->collection = $shipmentCollection;
        parent::__construct(['parameters' => $parameters]);
    }

    /**
     * The v2 (bulk) endpoint returns a link to a pdf that is prepared later, never the pdf itself.
     */
    public static function usesV2(ShipmentCollection $collection): bool
    {
        return $collection->count() >= self::LIMIT_TO_USE_V2;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers + ['Accept' => 'application/json;charset=utf8'];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = self::usesV2($this->collection) ? self::PATH_V2 : self::PATH;
        $ids  = $this->collection
            ->pluck('id')
            ->all();

        return strtr($path, [':ids' => implode(';', $ids)]);
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        $parameters              = $this->parameters;
        $parameters['positions'] = implode(';', $parameters['positions'] ?? []);

        return array_filter($parameters);
    }
}

