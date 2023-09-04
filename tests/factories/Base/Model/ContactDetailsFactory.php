<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of ContactDetails
 * @method ContactDetails make()
 * @method $this withAddress1(string $address1)
 * @method $this withAddress2(string $address2)
 * @method $this withArea(string $area)
 * @method $this withCc(string $cc)
 * @method $this withCity(string $city)
 * @method $this withCompany(string $company)
 * @method $this withEmail(string $email)
 * @method $this withPerson(string $person)
 * @method $this withPhone(string $phone)
 * @method $this withPostalCode(string $postalCode)
 * @method $this withRegion(string $region)
 * @method $this withState(string $state)
 */
final class ContactDetailsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ContactDetails::class;
    }

    public function inBelgium(): self
    {
        return $this->from(factory(Address::class)->inBelgium());
    }

    public function inFrance(): self
    {
        return $this->from(factory(Address::class)->inFrance());
    }

    public function inGermany(): self
    {
        return $this->from(factory(Address::class)->inGermany());
    }

    public function inTheNetherlands(): self
    {
        return $this->from(factory(Address::class)->inTheNetherlands());
    }

    public function inTheUnitedKingdom(): self
    {
        return $this->from(factory(Address::class)->inTheUnitedKingdom());
    }

    public function inTheUnitedStates(): self
    {
        return $this->from(factory(Address::class)->inTheUnitedStates());
    }

    public function withDifficultStreet(): self
    {
        return $this->from(factory(Address::class)->withDifficultStreet());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands()
            ->withCompany('MyParcel')
            ->withEmail('support@myparcel.nl')
            ->withPerson('Felicia Parcel');
    }
}
