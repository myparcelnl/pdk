<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi\Response;

/**
 * Mock response for CapabilitiesService::getCapabilities().
 *
 * Body shape: CapabilitiesResponsesCapabilitiesV2 -> {"results": [...]}
 * Each item is a RefCapabilitiesResponseCapabilityV2.
 *
 * Pass a custom $results array to the constructor to override defaults for a specific test:
 *   new ExampleCapabilitiesResponse([['carrier' => 'POSTNL', ...]])
 *
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesCapabilitiesV2
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2
 */
class ExampleCapabilitiesResponse extends SdkJsonResponse
{
    /**
     * @return array
     */
    protected function getContent(): array
    {
        return [
            'results' => $this->responseContent ?? [],
        ];
    }
}
