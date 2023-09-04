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
 */
final class RetailLocationFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return RetailLocation::class;
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
            ->withRetailNetworkId('PNPNL-01');
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands();
    }
}
