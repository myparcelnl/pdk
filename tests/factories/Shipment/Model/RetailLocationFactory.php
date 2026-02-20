<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of RetailLocation
 * @method RetailLocation make()
 * @method $this withBoxNumber(string $boxNumber)
 * @method $this withCc(string $cc)
 * @method $this withCity(string $city)
 * @method $this withLocationCode(string $locationCode)
 * @method $this withLocationName(string $locationName)
 * @method $this withNumber(string $number)
 * @method $this withNumberSuffix(string $numberSuffix)
 * @method $this withPostalCode(string $postalCode)
 * @method $this withRegion(string $region)
 * @method $this withRetailNetworkId(string $retailNetworkId)
 * @method $this withState(string $state)
 * @method $this withStreet(string $street)
 * @method $this withType(RetailLocationType $type)
 */
final class RetailLocationFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return RetailLocation::class;
    }

    public function inEU(): self
    {
        return $this
            ->withCc('DE')
            ->withCity('BERLIN')
            ->withPostalCode('1431ED')
            ->withStreet('Strasse')
            ->withNumber('1')
            ->withLocationCode('215700')
            ->withLocationName('Berghain')
            ->withRetailNetworkId('BGHN-01')
            ->withType(new RetailLocationType(RetailLocationType::PARCEL_POINT));
    }

    public function inTheNetherlands(): self
    {
        return $this
            ->withCc('NL')
            ->withCity('AALSMEER')
            ->withPostalCode('1431ED')
            ->withStreet('Zijdstraat')
            ->withNumber('38')
            ->withLocationCode('215795')
            ->withLocationName('Phone House Aalsmeer')
            ->withRetailNetworkId('PNPNL-01')
            ->withType(new RetailLocationType(RetailLocationType::PARCEL_LOCKER));
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands();
    }
}
