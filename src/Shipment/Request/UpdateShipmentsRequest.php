<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use InvalidArgumentException;
use MyParcelNL\Pdk\Account\Request\AbstractRequest;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

class UpdateShipmentsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/shipments';

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $ids;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $referenceIdentifiers;

    /**
     * @var null|int
     */
    private $size;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  null|int                                               $size
     */
    public function __construct(ShipmentCollection $collection, ?int $size = null)
    {
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
    public function getHttpMethod(): string
    {
        return 'GET';
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
    protected function getQueryParameters(): array
    {
        $referenceIdentifiers = $this->ids->isEmpty() ? $this->referenceIdentifiers->toArray() : [];

        return array_filter([
            'reference_identifier' => implode(';', $referenceIdentifiers),
            'size'                 => $this->size,
        ]);
    }
}
