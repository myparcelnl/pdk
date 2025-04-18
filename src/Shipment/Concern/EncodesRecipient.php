<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Support\Utils;

trait EncodesRecipient
{
    /**
     * @return null|array
     */
    private function encodeRecipient(?ContactDetails $recipient): ?array
    {
        if ($recipient === null) {
            return null;
        }

        return Utils::filterNull([
            'box_number'             => $recipient->boxNumber,
            'cc'                     => $recipient->cc,
            'city'                   => $recipient->city,
            'company'                => $recipient->company,
            'email'                  => $recipient->email,
            'number'                 => $recipient->number,
            'number_suffix'          => $recipient->numberSuffix,
            'person'                 => $recipient->person,
            'phone'                  => $recipient->phone,
            'postal_code'            => $recipient->postalCode,
            'region'                 => $recipient->region,
            'state'                  => $recipient->state,
            'street'                 => $recipient->street,
            'street_additional_info' => $recipient->streetAdditionalInfo,
            'eori_number'            => $recipient->eoriNumber,
            'vat_number'             => $recipient->vatNumber,
        ]);

    }
}
