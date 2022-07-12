<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * @property null|string $company
 * @property null|string $email
 * @property null|string $person
 * @property null|string $phone
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $fullStreet
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 * @property null|string $street
 * @property null|string $streetAdditionalInfo
 */
class ContactDetails extends Address
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['email']   = null;
        $this->attributes['phone']   = null;
        $this->attributes['person']  = null;
        $this->attributes['company'] = null;

        parent::__construct($data);
    }
}
