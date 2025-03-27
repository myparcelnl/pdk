<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class AddressValidateResponse extends ApiResponseWithBody
{
    /**
     * @var bool
     */
    private $valid = false;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return void
     */
    protected function parseResponseBody(): void
    {
        $data = json_decode($this->getBody() ?? '{"valid":false}', true);
        $this->valid = $data['valid'] ?? false;
    }
} 