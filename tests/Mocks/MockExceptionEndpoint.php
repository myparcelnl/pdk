<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use Exception;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedRequest;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\Base\Model\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mock endpoint that throws an exception during handling.
 */
class MockExceptionEndpoint extends AbstractEndpoint
{
    public function handle(Request $request): Response
    {
        throw new Exception('Something went wrong');
    }

    public function createVersionedRequest(Request $request, int $version): AbstractVersionedRequest
    {
        return mock(AbstractVersionedRequest::class);
    }

    public function createVersionedResource(Model $model, int $version): AbstractVersionedResource
    {
        return mock(AbstractVersionedResource::class);
    }
}
