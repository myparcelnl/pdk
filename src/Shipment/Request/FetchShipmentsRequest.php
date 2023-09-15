<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Sdk\src\Support\Collection;

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
     * @param  null|int $size
     */
    public function __construct(ShipmentCollection $shipmentCollection, private readonly ?int $size = null)
    {
        parent::__construct();
        $collection = new Collection($shipmentCollection->all());

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

    public function getPath(): string
    {
        if ($this->ids->isEmpty()) {
            return $this->path;
        }

        return sprintf('%s/%s', $this->path, implode(';', $this->ids->toArray()));
    }

    protected function getParameters(): array
    {
        $referenceIdentifiers = $this->ids->isEmpty() ? $this->referenceIdentifiers->toArray() : [];

        return array_filter([
            'reference_identifier' => implode(';', $referenceIdentifiers),
            'size'                 => $this->size,
        ]);
    }
}
