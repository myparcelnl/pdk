<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Request;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractV1Request;

/**
 * API v1 request validator and processor for delivery options requests.
 *
 * Handles orderId parameter validation and extraction for v1 API format.
 */
class GetDeliveryOptionsV1Request extends AbstractV1Request
{
    private ?string $orderId = null;

    /**
     * Validate the request according to this version's rules.
     */
    public function validate(): bool
    {
        $this->validationErrors = [];

        $this->orderId = $this->extractOrderId();

        if (! $this->orderId) {
            $this->addValidationError('orderId', 'Missing required parameter: orderId');
        }

        return empty($this->validationErrors);
    }

    /**
     * Get the validated order ID.
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Extract order ID from request.
     */
    private function extractOrderId(): ?string
    {
        $orderId = $this->httpRequest->query->get('orderId');

        return $orderId ? (string) $orderId : null;
    }
}
