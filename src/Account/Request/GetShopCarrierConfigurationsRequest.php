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

    /**
     * @var int
     */
    private $shopId;

    public function __construct(int $shopId)
    {
        parent::__construct();
        $this->shopId = $shopId;
    }

    public function getPath(): string
    {
        return strtr($this->path, [':shopId' => $this->shopId]);
    }
}
