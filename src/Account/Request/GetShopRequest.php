<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

class GetShopRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/shops';

    /**
     * @var int
     */
    private $shopId;

    /**
     * @param  int $shopId
     */
    public function __construct(int $shopId)
    {
        $this->shopId = $shopId;
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function getQueryParameters(): array
    {
        return [
            'id' => $this->shopId,
        ];
    }
}
