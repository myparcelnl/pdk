<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

class GetCarrierOptionsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/carrier_management/shops/:shopId/carrier_options';

    private   $shopId;

    public function __construct(int $shopId)
    {
        $this->shopId = $shopId;
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    public function getPath(): string
    {
        return strtr($this->path, [':shopId' => $this->shopId]);
    }
}
