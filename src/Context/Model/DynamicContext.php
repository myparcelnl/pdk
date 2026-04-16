<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\PrintOptionsView;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
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

        // These methods modify pluginSettings for display only — they do NOT write back to storage.
        // This is intentional: capabilities can change over time, so we resolve them fresh on each
        // page load rather than baking them into stored settings (where they'd be indistinguishable
        // from user choices and couldn't be updated when capabilities change).
        $this->ensureAllCarriersHaveSettings();
        $this->resolveCarrierCapabilities();

        if (\MyParcelNL\Pdk\Facade\Settings::get(LabelSettings::PROMPT, LabelSettings::ID)) {
            $this->attributes['printOptionsView'] = Pdk::get(PrintOptionsView::class);
        }

        $this->attributes['account']  = AccountSettings::getAccount();
        $this->attributes['carriers'] = AccountSettings::getCarriers();
        $this->attributes['shop']     = AccountSettings::getShop();
    }

    /**
     * Ensure every carrier from the account has an entry in the carrier settings collection.
     * The stored settings only contain carriers that the merchant has explicitly configured.
     * Missing carriers get a default CarrierSettings with all values at INHERIT (-1), which
     * resolveCarrierCapabilities() will then resolve to concrete display values.
     */
    private function ensureAllCarriersHaveSettings(): void
    {
        /** @var Settings $settings */
        $settings = $this->attributes['pluginSettings'];

        if (! $settings) {
            return;
        }

        $carriers = AccountSettings::getCarriers();

        foreach ($carriers as $carrier) {
            $carrierName = $carrier->carrier;

            if ($settings->carrier->firstWhere('id', $carrierName)) {
                continue;
            }

            $settings->carrier->offsetSet($carrierName, new CarrierSettings(['id' => $carrierName]));
        }
    }

    /**
     * Resolve carrier capabilities into display values for the frontend.
     *
     * The frontend carrier settings page uses ToggleInput components that only understand
     * on (1) and off (0) — not INHERIT (-1). This method resolves INHERIT values against
     * the current capabilities from the API:
     *
     * - isRequired: always forced to ENABLED regardless of stored value. Required options
     *   cannot be toggled off by the merchant — the view layer also makes these fields readonly.
     *
     * - isSelectedByDefault: when the stored value is INHERIT, resolve to the capability's
     *   default. This is done here at display time (not storage time) so that when capabilities
     *   change, the new default is reflected without being stuck behind a previously stored value.
     *
     * - Remaining INHERIT values (no capability info): resolved against the * global defaults
     *   from SettingsManager, falling back to DISABLED.
     */
    private function resolveCarrierCapabilities(): void
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

        $globalDefaults = $settings->carrier->firstWhere('id', SettingsManager::KEY_ALL);

        foreach ($settings->carrier as $carrierSettings) {
            if ($carrierSettings->id === SettingsManager::KEY_ALL) {
                continue;
            }

            $carrier = $carrierRepository->find($carrierSettings->id);

            foreach ($definitions as $definition) {
                $capabilitiesKey    = $definition->getCapabilitiesOptionsKey();
                $carrierSettingsKey = $definition->getCarrierSettingsKey();

                if (! $carrierSettingsKey) {
                    continue;
                }

                $option       = $carrier && $capabilitiesKey ? $carrier->getOptionMetadata($capabilitiesKey) : null;
                $currentValue = $carrierSettings->getAttribute($carrierSettingsKey);

                // isRequired: force ENABLED regardless of stored value
                if ($option && $option->getIsRequired()) {
                    $carrierSettings->setAttribute($carrierSettingsKey, TriStateService::ENABLED);
                    continue;
                }

                if ($currentValue !== TriStateService::INHERIT) {
                    continue;
                }

                // isSelectedByDefault: resolve INHERIT against current capability default
                if ($option && $option->getIsSelectedByDefault()) {
                    $carrierSettings->setAttribute($carrierSettingsKey, TriStateService::ENABLED);
                    continue;
                }

                // Fall back to * global defaults, then DISABLED
                $globalValue = $globalDefaults
                    ? $globalDefaults->getAttribute($carrierSettingsKey)
                    : TriStateService::INHERIT;

                $carrierSettings->setAttribute(
                    $carrierSettingsKey,
                    $globalValue !== TriStateService::INHERIT ? $globalValue : TriStateService::DISABLED
                );
            }
        }
    }
}
