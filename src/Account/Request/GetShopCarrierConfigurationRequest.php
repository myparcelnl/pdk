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

    public function __construct(private readonly int $shopId, private readonly string $carrier)
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return strtr($this->path, [
            ':shopId'  => $this->shopId,
            ':carrier' => $this->carrier,
        ]);
    }
}
