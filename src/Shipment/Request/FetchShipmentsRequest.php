<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

class FetchShipmentsRequest extends Request
{
    /**
     * @var string
     */
    protected $path = '/shipments';

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $ids;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $referenceIdentifiers;

    /**
     * @var null|int
     */
    private $size;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipmentCollection
     * @param  null|int                                               $size
     */
    public function __construct(ShipmentCollection $shipmentCollection, ?int $size = null)
    {
        parent::__construct();
        $collection = new Collection($shipmentCollection->all());

        $this->size = $size;

        $this->ids                  = $collection->pluck('id')
            ->filter();
        $this->referenceIdentifiers = $collection->pluck('referenceIdentifier')
            ->filter();

        if ($this->ids->isEmpty() && $this->referenceIdentifiers->isEmpty()) {
            throw new InvalidArgumentException(
                'At least one Shipment should contain either an id or a reference_identifier to use update().'
            );
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        if ($this->ids->isEmpty()) {
            return $this->path;
        }

        return sprintf('%s/%s', $this->path, implode(';', $this->ids->toArray()));
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        $referenceIdentifiers = $this->ids->isEmpty() ? $this->referenceIdentifiers->toArray() : [];

        return array_filter([
            'reference_identifier' => implode(';', $referenceIdentifiers),
            'size'                 => $this->size,
        ]);
    }
}
