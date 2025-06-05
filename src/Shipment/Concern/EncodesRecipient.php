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

        $maxStreetLength = 40;
        $maxStreetAdditionalInfoLength = 50;

        $street = (string) $recipient->street;
        $streetAdditionalInfo = (string) $recipient->streetAdditionalInfo;

        // Truncate street name and put overflow in street_additional_info
        if (mb_strlen($street) > $maxStreetLength) {
            $overflow = mb_substr($street, $maxStreetLength);
            $street = mb_substr($street, 0, $maxStreetLength);

            // Add overflow to street_additional_info if possible
            $streetAdditionalInfo = $overflow . $streetAdditionalInfo;
        }

        // Truncate street_additional_info
        if (mb_strlen($streetAdditionalInfo) > $maxStreetAdditionalInfoLength) {
            $streetAdditionalInfo = mb_substr($streetAdditionalInfo, 0, $maxStreetAdditionalInfoLength);
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
            'street'                 => $street,
            'street_additional_info' => $streetAdditionalInfo,
            'eori_number'            => $recipient->eoriNumber,
            'vat_number'             => $recipient->vatNumber,
        ]);

    }
}
