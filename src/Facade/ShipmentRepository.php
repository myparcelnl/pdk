<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

/**
 * @method static ShipmentCollection createConcepts(ShipmentCollection $collection)
 * @method static ShipmentCollection getByReferenceIdentifiers(array $referenceIdentifiers, ?int $size = null)
 * @method static ShipmentCollection fetchLabelPdf(ShipmentCollection $collection, ?string $format, ?array $position)
 * @method static ShipmentCollection fetchLabelLink(ShipmentCollection $collection, ?string $format, ?array $position)
 * @method static ShipmentCollection query(array $parameters)
 * @method static ShipmentCollection update(ShipmentCollection $collection, ?int $size = null)
 * @implements \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
 */
class ShipmentRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::class;
    }
}
