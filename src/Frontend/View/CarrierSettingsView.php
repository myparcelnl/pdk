<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CarrierSettingsView extends AbstractSettingsView
{
    private $carriers;

    public function __construct()
    {
        $this->carriers = AccountSettings::getCarrierOptions();
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \Exception
     */
    protected function createChildren(): ?Collection
    {
        $array = [];

        $this->carriers->each(static function (CarrierOptions $carrierOptions) use (&$array) {
            $view    = new CarrierSettingsItemView($carrierOptions);
            $array[] = ['id' => $carrierOptions->carrier->name] + $view->toArray();
        });

        return new Collection($array);
    }

    /**
     * @return null|\MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function createElements(): ?FormElementCollection
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
