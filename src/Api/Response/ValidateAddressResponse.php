<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

/**
 * Response for address validation requests
 */
class ValidateAddressResponse extends AddressResponse
{
    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->data['valid'] ?? false;
    }

    /**
     * @return void
     */
    protected function parseResponseBody(): void
    {
        parent::parseResponseBody();
        if (!isset($this->data['valid'])) {
            $this->data['valid'] = false;
        }
        $this->data = ['valid' => $this->data['valid']];
    }
}
