<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Factory\ShipmentValidatorFactory;
use MyParcelNL\Sdk\src\Concerns\HasApiKey;
use MyParcelNL\Sdk\src\Support\Collection;
use MyParcelNL\Sdk\src\Validator\AbstractValidator;
use RuntimeException;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\Shipment[] $items
 */
class ShipmentCollection extends Collection
{
    use HasApiKey;

    /**
     * @return void
     * @throws \Exception
     */
    public function export(): void
    {
        $this->validateShipments();
        // Todo: export to myparcel
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function validateShipments(): void
    {
        if (! $this->count()) {
            throw new RuntimeException('Add shipments to the collection before exporting.');
        }

        $validators = new Collection();

        foreach ($this->items as $shipment) {
            $validator = $validators->firstWhere('carrier', $shipment->carrier);

            if (! $validator) {
                $validator = ShipmentValidatorFactory::create($shipment->carrier);

                $validators->push($validator);
            }

            $validator->validateAll($shipment);
        }

        $validators
            ->each(function (AbstractValidator $validator) {
                $validator->report();
            });
    }
}
