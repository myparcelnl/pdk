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
    private $id;

    /**
     * @param  int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function getQueryParameters(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
