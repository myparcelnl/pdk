<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Sdk\Support\Str;
use MyParcelNL\Pdk\Base\Support\Utils;

trait EncodesRecipient
{
    private static int $maxStreetLength = 40;
    private static int $maxStreetAdditionalInfoLength = 50;

    /**
     * @return null|array
     */
    private function encodeRecipient(?ContactDetails $recipient): ?array
    {
        if ($recipient === null) {
            return null;
        }

        $street = $recipient->street;
        $streetAdditionalInfo = $recipient->streetAdditionalInfo;

        // Remove overlap if extra info starts with the end of street
        [$street, $streetAdditionalInfo] = $this->removeStreetOverlap($street, $streetAdditionalInfo);

        // Always truncate street to max length
        [$street, $overflow] = $this->truncateStreet($street, self::$maxStreetLength);

        // Add overflow to extra field only if it is empty
        if ($overflow && empty($streetAdditionalInfo)) {
            $streetAdditionalInfo = $overflow;
        }

        // Truncate extra field if needed
        $streetAdditionalInfo = $this->truncateExtra($streetAdditionalInfo, self::$maxStreetAdditionalInfoLength);

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

    /**
     * Truncates the street and returns the truncated street and overflow.
     */
    private function truncateStreet(?string $street, int $maxLength): array
    {
        if ($street && Str::length($street) > $maxLength) {
            $overflow = trim(Str::substr($street, $maxLength));
            $street = trim(Str::limit($street, $maxLength, ''));
            return [$street, $overflow];
        }
        return [$street, null];
    }

    /**
     * Truncates the extra street info if needed.
     */
    private function truncateExtra(?string $extra, int $maxLength): ?string
    {
        if ($extra && Str::length($extra) > $maxLength) {
            return Str::limit($extra, $maxLength, '');
        }
        return $extra;
    }

    /**
     * Removes overlap between the end of street and the start of extra info.
     */
    private function removeStreetOverlap(?string $street, ?string $extra): array
    {
        if ($street && $extra) {
            $overlapLength = min(self::$maxStreetLength, Str::length($extra));
            $streetEnd = Str::substr($street, -$overlapLength);
            $infoStart = Str::substr($extra, 0, $overlapLength);
            if (Str::lower($streetEnd) === Str::lower($infoStart)) {
                $street = Str::substr($street, 0, Str::length($street) - $overlapLength);
                $street = rtrim($street, ', ');
            }
        }
        return [$street, $extra];
    }
}
