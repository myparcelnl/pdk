<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

class GetLabelsAsPdfRequest extends GetLabelsRequest
{
    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return array_replace(
            parent::getHeaders(),
            [
                'Accept' => 'application/pdf',
            ]
        );
    }
}
