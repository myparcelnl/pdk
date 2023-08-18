<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Frontend;

final class MockFrontendEndpointService extends AbstractFrontendEndpointService
{
    public function getBaseUrl(): string
    {
        return 'FRONTEND_URL';
    }
}
