<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Backend;

final class MockBackendEndpointService extends AbstractPdkBackendEndpointService
{
    public function getBaseUrl(): string
    {
        return 'BACKEND_URL';
    }
}
