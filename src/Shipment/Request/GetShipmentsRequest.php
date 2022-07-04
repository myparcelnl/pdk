<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Account\Request\AbstractRequest;

class GetShipmentsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/shipments';

    /**
     * @var null|string
     */
    private $referenceIdentifier;

    /**
     * @param  null|string $referenceIdentifier
     */
    public function __construct(string $referenceIdentifier = null)
    {
        $this->referenceIdentifier = $referenceIdentifier;
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function getQueryParameters(): array
    {
        return array_filter(
            [
                'reference_identifier' => $this->referenceIdentifier,
            ]
        );
    }
}
