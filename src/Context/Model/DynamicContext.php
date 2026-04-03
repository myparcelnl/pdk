<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\PrintOptionsView;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property Account           $account
 * @property CarrierCollection $carriers
 * @property Settings          $pluginSettings
 * @property Shop              $shop
 */
class DynamicContext extends Model
{
    public $attributes = [
        'account'          => null,
        'carriers'         => null,
        'pluginSettings'   => null,
        'printOptionsView' => null,
        'shop'             => null,
    ];

    protected $casts      = [
        'account'          => Account::class,
        'carriers'         => CarrierCollection::class,
        'pluginSettings'   => Settings::class,
        'printOptionsView' => PrintOptionsView::class,
        'shop'             => Shop::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
        $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

        $this->attributes['pluginSettings'] = $settingsRepository->all();

        $this->enforceRequiredCarrierSettings();

        if (\MyParcelNL\Pdk\Facade\Settings::get(LabelSettings::PROMPT, LabelSettings::ID)) {
            $this->attributes['printOptionsView'] = Pdk::get(PrintOptionsView::class);
        }

        $this->attributes['account']  = AccountSettings::getAccount();
        $this->attributes['carriers'] = AccountSettings::getCarriers();
        $this->attributes['shop']     = AccountSettings::getShop();
    }

    /**
     * Force carrier settings values to ENABLED for options where the carrier capability has isRequired=true.
     * This ensures the frontend displays required options as enabled, without modifying the stored settings.
     */
    private function enforceRequiredCarrierSettings(): void
    {
        /** @var Settings $settings */
        $settings = $this->attributes['pluginSettings'];

        if (! $settings || ! $settings->carrier) {
            return;
        }

        /** @var CarrierRepositoryInterface $carrierRepository */
        $carrierRepository = Pdk::get(CarrierRepositoryInterface::class);

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($settings->carrier as $carrierSettings) {
            $carrier = $carrierRepository->find($carrierSettings->id);

            if (! $carrier) {
                continue;
            }

            foreach ($definitions as $definition) {
                $capabilitiesKey    = $definition->getCapabilitiesOptionsKey();
                $carrierSettingsKey = $definition->getCarrierSettingsKey();

                if (! $capabilitiesKey || ! $carrierSettingsKey) {
                    continue;
                }

                $option = $carrier->getOptionMetadata($capabilitiesKey);

                if ($option && $option->getIsRequired()) {
                    $carrierSettings->setAttribute($carrierSettingsKey, TriStateService::ENABLED);
                }
            }
        }
    }
}
