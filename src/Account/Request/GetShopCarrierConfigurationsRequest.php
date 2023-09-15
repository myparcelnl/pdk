<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShopCarrierConfigurationsRequest extends Request
{
    /**
     * @var string
     */
    protected $path = '/shops/:shopId/carrier_configurations';

    public function __construct(private readonly int $shopId)
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return strtr($this->path, [':shopId' => $this->shopId]);
    }
}
