<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedRequest;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\Base\Model\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mock endpoint that fails validation.
 */
class MockValidationFailureEndpoint extends AbstractEndpoint
{
    public function validate(Request $request): bool
    {
        return false;
    }

    public function handle(Request $request): Response
    {
        return new Response('should not reach this', 200);
    }

    protected function createVersionedRequest(Request $request, int $version): AbstractVersionedRequest
    {
        return mock(AbstractVersionedRequest::class);
    }

    protected function createVersionedResource(Model $model, int $version): AbstractVersionedResource
    {
        return mock(AbstractVersionedResource::class);
    }
}
