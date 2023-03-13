<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShopCarrierConfigurationRequest extends Request
{
    /**
     * @var string
     */
    protected $path = '/shops/:shopId/carriers/:carrier/carrier_configuration';

    /**
     * @var string
     */
    private $carrier;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(int $shopId, string $carrier)
    {
        parent::__construct();
        $this->shopId  = $shopId;
        $this->carrier = $carrier;
    }

    public function getPath(): string
    {
        return strtr($this->path, [
            ':shopId'  => $this->shopId,
            ':carrier' => $this->carrier,
        ]);
    }
}
