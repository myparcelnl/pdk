<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Api\Backend\AbstractPdkBackendEndpointService;

class MockBackendEndpointService extends AbstractPdkBackendEndpointService
{
    public function getBaseUrl(): string
    {
        return 'BACKEND_URL';
    }
}
