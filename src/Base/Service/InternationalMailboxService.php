<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\InternationalMailboxServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class InternationalMailboxService implements InternationalMailboxServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    public function internationalMailboxPossible(Carrier $carrier): bool
    {
        if (! $carrier->canHaveInternationalMailbox()) {
            return false;
        }
        $settings = Settings::all()->carrier->get($carrier->externalIdentifier);

        return $settings->allowInternationalMailbox ?? false;
    }

    /**
     * @param  null|string $cc
     * @param  string      $packageTypeName
     *
     * @return bool
     */
    public function isInternationalMailbox(?string $cc, string $packageTypeName): bool
    {
        if ($packageTypeName !== DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME) {
            return false;
        }

        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);

        $shippingZone = $cc ? $countryService->getShippingZone($cc) : null;

        return $shippingZone !== 'NL' && $shippingZone !== 'BE';
    }
}
