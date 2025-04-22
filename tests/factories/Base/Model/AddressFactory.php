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
            ->withStreet('Adriaan Brouwerstraat')
            ->withNumber('16')
            ->withCc('BE')
            ->withCity('Antwerpen')
            ->withPostalCode('1000');
    }

    public function inFrance(): self
    {
        return $this
            ->withStreet('Rue de Rivoli')
            ->withNumber('1')
            ->withCc('FR')
            ->withCity('Paris')
            ->withPostalCode('75001');
    }

    public function inGermany(): self
    {
        return $this
            ->withStreet('Musterstrasse 1')
            ->withNumber(false) // workaround: cannot set to null and is otherwise inherited from earlier instances
            ->withCc('DE')
            ->withCity('Berlin')
            ->withPostalCode('10117');
    }

    public function inTheNetherlands(): self
    {
        return $this
            ->withStreet('Antareslaan')
            ->withNumber('31')
            ->withCc('NL')
            ->withCity('Hoofddorp')
            ->withPostalCode('2132 JE');
    }

    public function inTheUnitedKingdom(): self
    {
        return $this
            ->withStreet('1 Primrose Street')
            ->withNumber(false) // workaround: cannot set to null and is otherwise inherited from earlier instances
            ->withCc('GB')
            ->withCity('London')
            ->withPostalCode('EC2A 2EX');
    }

    public function inTheUnitedStates(): self
    {
        return $this
            ->withStreet('1 Infinite Loop')
            ->withNumber(false) // workaround: cannot set to null and is otherwise inherited from earlier instances
            ->withCc('US')
            ->withCity('Cupertino')
            ->withState('CA')
            ->withPostalCode('95014');
    }

    public function withDifficultStreet(): self
    {
        return $this->withStreet('Plein 1940-45 3');
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands();
    }
}
