<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

/**
 * Base class for address related responses
 */
abstract class AddressResponse extends ApiResponseWithBody
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return void
     */
    protected function parseResponseBody(): void
    {
        $this->data = json_decode($this->getBody() ?? '{}', true) ?? [];
    }
}
