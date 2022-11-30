<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Base\Support\Arr;

class MockSettingsRepository extends AbstractSettingsRepository
{
    private const DEFAULT_SETTINGS = [
        GeneralSettings::ID => [
            GeneralSettings::API_KEY                    => 'b03ad4237eab5bed119257012a4c5866',
            GeneralSettings::API_LOGGING                => false,
            GeneralSettings::SHARE_CUSTOMER_INFORMATION => true,
        ],

        CheckoutSettings::ID => [
            CheckoutSettings::SHOW_PRICE_AS_SURCHARGE  => true,
            CheckoutSettings::STRING_ADDRESS_NOT_FOUND => 'Address not found',
        ],

        CarrierSettings::ID => [
            [
                CarrierSettings::CARRIER_NAME           => CarrierOptions::CARRIER_POSTNL_NAME,
                CarrierSettings::ALLOW_SIGNATURE        => true,
                CarrierSettings::PRICE_SIGNATURE        => 80,
                CarrierSettings::DROP_OFF_POSSIBILITIES => [
                    'dropOffDays' => [
                        [
                            'cutoffTime'        => '17:00',
                            'sameDayCutoffTime' => null,
                            'weekday'           => DropOffDay::WEEKDAY_MONDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => '15:00',
                            'sameDayCutoffTime' => '10:00',
                            'weekday'           => DropOffDay::WEEKDAY_TUESDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => '17:00',
                            'sameDayCutoffTime' => null,
                            'weekday'           => DropOffDay::WEEKDAY_WEDNESDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => '15:00',
                            'sameDayCutoffTime' => '10:00',
                            'weekday'           => DropOffDay::WEEKDAY_THURSDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => '17:00',
                            'sameDayCutoffTime' => '09:00',
                            'weekday'           => DropOffDay::WEEKDAY_FRIDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => '15:30',
                            'sameDayCutoffTime' => '10:00',
                            'weekday'           => DropOffDay::WEEKDAY_SATURDAY,
                            'dispatch'          => true,
                        ],
                        [
                            'cutoffTime'        => null,
                            'sameDayCutoffTime' => null,
                            'weekday'           => DropOffDay::WEEKDAY_SUNDAY,
                            'dispatch'          => false,
                        ],
                    ],
                ],
            ],
            [
                CarrierSettings::ALLOW_SAME_DAY_DELIVERY => true,
                CarrierSettings::CARRIER_NAME            => CarrierOptions::CARRIER_INSTABOX_NAME,
            ],
            [
                CarrierSettings::CARRIER_NAME => null,
            ],
        ],
    ];

    /**
     * @var array
     */
    private $saved = self::DEFAULT_SETTINGS;

    /**
     * @var \MyParcelNL\Pdk\Settings\Model\Settings
     */
    private $settings;

    /**
     * @param  array                                           $settings
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage      $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $settings = [], MemoryCacheStorage $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage, $api);

        foreach ($settings as $key => $value) {
            Arr::set($this->saved, $key, $value);
        }

        $this->settings = $this->getFromStorage();
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getFromStorage(): Settings
    {
        return new Settings($this->saved);
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return $this
     */
    public function set(Settings $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function store(Settings $settings): void
    {
        $this->saved = $settings->toArray();
    }
}
