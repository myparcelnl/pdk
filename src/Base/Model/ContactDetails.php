<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * Extend the Address class to add personal contact details.
 *
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $person
 * @property string|null $company
 * @property string|null $eoriNumber
 * @property string|null $vatNumber
 *
 * @inheritDoc
 */
class ContactDetails extends Address
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
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
        $this->casts['eoriNumber'] = 'string';
        $this->casts['vatNumber']  = 'string';

        parent::__construct($data);
    }
}
