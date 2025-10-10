<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CarrierSettingsView extends AbstractSettingsView
{
    private $carriers;

    public function __construct()
    {
        $this->carriers = AccountSettings::getCarriers();
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \Exception
     */
    protected function createChildren(): ?Collection
    {
        $array = [];

        $this->carriers->each(function (Carrier $carrier) use (&$array) {
            $view    = new CarrierSettingsItemView($carrier);
            $legacyId = FrontendData::getLegacyIdentifier($carrier->externalIdentifier);
            $array[] = ['id' => $legacyId] + $view->toArray();
        });

        return new Collection($array);
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return CarrierSettings::ID;
    }
}
