<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;

class SettingsManager implements SettingsManagerInterface
{
    /**
     * This key is used to store global settings that apply to all sub items of a collection settings group. Currently
     * only applies to carrier settings.
     */
    public const KEY_ALL = '*';

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    protected $repository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $repository
     */
    public function __construct(PdkSettingsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function all(): Settings
    {
        return $this->repository->all();
    }

    /**
     * @param  string      $key
     * @param  null|string $namespace
     * @param  mixed       $default
     *
     * @return mixed
     */
    public function get(string $key, ?string $namespace = null, $default = null)
    {
        if ($namespace) {
            $key = sprintf('%s.%s', $namespace, $key);
        }

        return $this->repository->get(Pdk::get('createSettingsKey')($key)) ?? $default;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getDefaults(): array
    {
        $defaults = Utils::toRecursiveCollection(Pdk::get('mergedDefaultSettings') ?? []);

        $this->applyCarrierDefaults($defaults);

        return $defaults->toArray();
    }

    /**
     * Fill the carrier settings array with defaults for all allowed carriers.
     *
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $defaults
     *
     * @return void
     */
    protected function applyCarrierDefaults(Collection $defaults): void
    {
        if (! $defaults->has(CarrierSettings::ID)) {
            return;
        }

        /** @var Collection $carrierSettings */
        $carrierSettings = $defaults->get(CarrierSettings::ID);

        $keyedCarriers = Platform::getCarriers()
            ->keyBy('name');

        // add any keys that are not present yet from allowed carriers to $carrierSettings
        (new Collection($keyedCarriers))->keys()
            ->diff($carrierSettings->keys())
            ->each(function ($carrier) use ($carrierSettings) {
                $carrierSettings->put($carrier, new Collection());
            });

        /** @var Collection $globalDefaults */
        $globalDefaults = $carrierSettings->get(self::KEY_ALL);

        // Apply the global defaults to all carriers
        $mergedCarrierSettings = $carrierSettings
            ->map(static function (Collection $settings, string $key) use ($globalDefaults) {
                if (self::KEY_ALL === $key) {
                    return $settings;
                }

                return $globalDefaults->merge($settings);
            });

        $defaults->put(CarrierSettings::ID, $mergedCarrierSettings);
    }
}
