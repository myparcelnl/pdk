<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Api\Frontend\AbstractFrontendEndpointService;

class MockFrontendEndpointService extends AbstractFrontendEndpointService
{
    public function getBaseUrl(): string
    {
        return 'FRONTEND_URL';
    }
}
