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

    public function inTheNetherlands()
    {
        return $this->from(factory(Address::class)->inTheNetherlands());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->inTheNetherlands()
            ->withCompany('MyParcel')
            ->withEmail('support@myparcel.nl')
            ->withPerson('Felicia Parcel');
    }
}
