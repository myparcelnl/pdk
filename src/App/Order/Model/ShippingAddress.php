<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;

/**
 * @property string|null $eoriNumber
 * @property string|null $vatNumber
 * @property null|string $company
 * @property null|string $email
 * @property null|string $person
 * @property null|string $phone
 * @property null|string $address1
 * @property null|string $address2
 * @property null|string $area
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 */
class ShippingAddress extends ContactDetails
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['eoriNumber'] = null;
        $this->attributes['vatNumber']  = null;

        $this->casts['eoriNumber'] = 'string';
        $this->casts['vatNumber']  = 'string';

        parent::__construct($data);
    }
}
