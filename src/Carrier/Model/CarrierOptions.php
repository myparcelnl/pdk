<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCapabilitiesCollection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Sdk\src\Support\Arr;

/**
 * @property null|int                                                         $id
 * @property null|string                                                      $name
 * @property null|string                                                      $human
 * @property null|int                                                         $subscriptionId
 * @property null|bool                                                        $primary
 * @property null|bool                                                        $isDefault
 * @property null|bool                                                        $optional
 * @property null|string                                                      $label
 * @property null|string                                                      $type
 * @property \MyParcelNL\Pdk\Carrier\Collection\CarrierCapabilitiesCollection $options
 * @property \MyParcelNL\Pdk\Carrier\Collection\CarrierCapabilitiesCollection $returnOptions
 */
class CarrierOptions extends Model
{
    public const  CARRIER_POSTNL_ID      = 1;
    public const  CARRIER_BPOST_ID       = 2;
    public const  CARRIER_DPD_ID         = 4;
    public const  CARRIER_INSTABOX_ID    = 5;
    public const  CARRIER_POSTNL_NAME    = 'postnl';
    public const  CARRIER_BPOST_NAME     = 'bpost';
    public const  CARRIER_DPD_NAME       = 'dpd';
    public const  CARRIER_INSTABOX_NAME  = 'instabox';
    public const  TYPE_CUSTOM            = 'custom';
    public const  TYPE_MAIN              = 'main';
    private const ORDERED_CARRIER_GETTER = [
        'subscriptionId' => self::TYPE_CUSTOM,
        'id'             => self::TYPE_MAIN,
        'name'           => self::TYPE_MAIN,
    ];

    protected $attributes = [
        'id'                 => null,
        'name'               => null,
        'human'              => null,
        'subscriptionId'     => null,
        'primary'            => null,
        'isDefault'          => null,
        'optional'           => null,
        'label'              => null,
        'type'               => null,
        'capabilities'       => CarrierCapabilitiesCollection::class,
        'returnCapabilities' => CarrierCapabilitiesCollection::class,
    ];

    protected $casts      = [
        'id'                 => 'int',
        'name'               => 'string',
        'human'              => 'string',
        'subscriptionId'     => 'int',
        'primary'            => 'bool',
        'isDefault'          => 'bool',
        'optional'           => 'bool',
        'label'              => 'string',
        'type'               => 'string',
        'capabilities'       => CarrierCapabilitiesCollection::class,
        'returnCapabilities' => CarrierCapabilitiesCollection::class,
    ];

    /**
     * @param  null|array $data
     *
     * @throws \Exception
     */
    public function __construct(?array $data = null)
    {
        if ($data) {
            $data = $this->create($data);
        }

        parent::__construct($data);
    }

    /**
     * @param  array $data
     *
     * @return array|mixed
     */
    private function create(array $data)
    {
        $carrierConfig = Config::get('carriers');

        $value = $data['subscriptionId'] ?? $data['id'] ?? $data['name'];

        foreach (self::ORDERED_CARRIER_GETTER as $key => $type) {
            $createdCarrier = Arr::first($carrierConfig, static function ($carrier) use ($key, $value, $type) {
                return ($value === ($carrier[$key] ?? null) && $type === ($carrier['type'] ?? null));
            });

            if ($createdCarrier) {
                return $createdCarrier;
            }
        }

        DefaultLogger::warning('Could not find a matching carrier', [
            'input' => $data,
        ]);

        return [];
    }
}
