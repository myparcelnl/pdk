<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class GetShopCarrierOptionsRequest extends Request
{
    protected $path = '/carrier_management/shops/:shopId/carrier_options';

    /**
     * @var int
     */
    private $shopId;

    /**
     * @param  int $shopId
     */
    public function __construct(int $shopId)
    {
        parent::__construct();
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return strtr($this->path, [':shopId' => $this->shopId]);
    }
}
