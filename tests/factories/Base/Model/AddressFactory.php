<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Address
 * @method Address make()
 * @method $this withAddress1(string $address1)
 * @method $this withAddress2(string $address2)
 * @method $this withArea(string $area)
 * @method $this withCc(string $cc)
 * @method $this withCity(string $city)
 * @method $this withPostalCode(string $postalCode)
 * @method $this withRegion(string $region)
 * @method $this withState(string $state)
 */
final class AddressFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Address::class;
    }

    public function inBelgium(): self
    {
        return $this
            ->withAddress1('Adriaan Brouwerstraat 16')
            ->withCc('BE')
            ->withCity('Antwerpen')
            ->withPostalCode('1000');
    }

    public function inFrance(): self
    {
        return $this
            ->withAddress1('Rue de Rivoli 1')
            ->withCc('FR')
            ->withCity('Paris')
            ->withPostalCode('75001');
    }

    public function inGermany(): self
    {
        return $this
            ->withAddress1('Musterstrasse 1')
            ->withCc('DE')
            ->withCity('Berlin')
            ->withPostalCode('10117');
    }

    public function inTheNetherlands(): self
    {
        return $this
            ->withAddress1('Antareslaan 31')
            ->withCc('NL')
            ->withCity('Hoofddorp')
            ->withPostalCode('2132 JE');
    }

    public function inTheUnitedKingdom(): self
    {
        return $this
            ->withAddress1('1 Primrose Street')
            ->withCc('GB')
            ->withCity('London')
            ->withPostalCode('EC2A 2EX');
    }

    public function inTheUnitedStates(): self
    {
        return $this
            ->withAddress1('1 Infinite Loop')
            ->withCc('US')
            ->withCity('Cupertino')
            ->withState('CA')
            ->withPostalCode('95014');
    }

    public function withDifficultStreet(): self
    {
        return $this->withAddress1('Plein 1940-45 3');
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands();
    }
}
