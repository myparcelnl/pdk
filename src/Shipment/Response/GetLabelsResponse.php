<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetLabelsResponse extends ApiResponseWithBody
{
    /**
     * @var string
     */
    private $labelLink;

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->labelLink;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody      = json_decode($this->getBody(), true);
        $responseKey     = array_key_exists('pdf', $parsedBody['data']) ? 'pdf' : 'pdfs';
        $link            = $parsedBody['data'][$responseKey];
        // The bulk (v2) endpoint returns the link as a list or as a single object.
        $this->labelLink = $link['url'] ?? $link[0]['url'];
    }
}
