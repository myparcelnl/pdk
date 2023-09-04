<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of ShippingAddress
 * @method ShippingAddress make()
 * @method $this withAddress1(string $address1)
 * @method $this withAddress2(string $address2)
 * @method $this withArea(string $area)
 * @method $this withCc(string $cc)
 * @method $this withCity(string $city)
 * @method $this withCompany(string $company)
 * @method $this withEmail(string $email)
 * @method $this withEoriNumber(string $eoriNumber)
 * @method $this withPerson(string $person)
 * @method $this withPhone(string $phone)
 * @method $this withPostalCode(string $postalCode)
 * @method $this withRegion(string $region)
 * @method $this withState(string $state)
 * @method $this withVatNumber(string $vatNumber)
 */
final class ShippingAddressFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShippingAddress::class;
    }

    public function inBelgium(): self
    {
        return $this->from(factory(ContactDetails::class)->inBelgium());
    }

    public function inFrance(): self
    {
        return $this->from(factory(ContactDetails::class)->inFrance());
    }

    public function inGermany(): self
    {
        return $this->from(factory(ContactDetails::class)->inGermany());
    }

    public function inTheNetherlands(): self
    {
        return $this->from(factory(ContactDetails::class)->inTheNetherlands());
    }

    public function inTheUnitedKingdom(): self
    {
        return $this->from(factory(ContactDetails::class)->inTheUnitedKingdom());
    }

    public function inTheUnitedStates(): self
    {
        return $this->from(factory(ContactDetails::class)->inTheUnitedStates());
    }

    public function withDifficultStreet(): self
    {
        return $this->from(factory(ContactDetails::class)->withDifficultStreet());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->inTheNetherlands()
            ->withVatNumber('NL123456789B01');
    }
}
