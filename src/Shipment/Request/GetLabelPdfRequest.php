<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

/**
 * Downloads a labels pdf that the bulk labels endpoint prepared under /pdfs/:labelId.
 */
class GetLabelPdfRequest extends Request
{
    /**
     * @var string
     */
    private $labelId;

    public function __construct(string $labelId)
    {
        $this->labelId = $labelId;
        parent::__construct();
    }

    public function getHeaders(): array
    {
        return $this->headers + ['Accept' => 'application/pdf'];
    }

    public function getPath(): string
    {
        return "pdfs/$this->labelId";
    }
}
