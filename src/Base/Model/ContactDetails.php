<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * @property null|string $company
 * @property null|string $email
 * @property null|string $eoriNumber
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
 * @property null|string $vatNumber
 */
class ContactDetails extends Address
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        $this->attributes['email']      = null;
        $this->attributes['phone']      = null;
        $this->attributes['person']     = null;
        $this->attributes['company']    = null;
        $this->attributes['eoriNumber'] = null;
        $this->attributes['vatNumber']  = null;

        $this->casts['email']      = 'string';
        $this->casts['phone']      = 'string';
        $this->casts['person']     = 'string';
        $this->casts['company']    = 'string';
        $this->casts['eoriNumber'] = '';
        $this->casts['vatNumber']  = 'string';

        parent::__construct($data);
    }
}
