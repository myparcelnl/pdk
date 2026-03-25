<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\UnsupportedVersionException;
use MyParcelNL\Pdk\App\Endpoint\Contract\VersionedRequestInterface;
use MyParcelNL\Pdk\App\Endpoint\Contract\VersionedResourceInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

// Concrete test implementation of AbstractEndpoint for testing
class TestEndpoint extends AbstractEndpoint
{
    public function getSupportedVersions(): array
    {
        return [1, 2];
    }

    protected function handleVersionedRequest(Request $request, int $version): Response
    {
        return new Response('test response');
    }

    public function createVersionedRequest(Request $request, int $version): VersionedRequestInterface
    {
        return mock(VersionedRequestInterface::class);
    }

    public function createVersionedResource($model, int $version): VersionedResourceInterface
    {
        return mock(VersionedResourceInterface::class);
    }

    // Expose protected methods for testing
    public function testDetectVersion(Request $request): int
    {
        return $this->detectVersion($request);
    }

    public function testExtractVersionFromHeader(string $header): ?int
    {
        return $this->extractVersionFromHeader($header);
    }
}

it('handles a request and returns a symfony response', function () {
    $endpoint = new TestEndpoint();
    $request = new Request();

    $response = $endpoint->handle($request);

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBe('test response');
});

it('creates a versioned request object', function () {
    $endpoint = new TestEndpoint();
    $request = new Request();

    $versionedRequest = $endpoint->createVersionedRequest($request, 1);

    expect($versionedRequest)->toBeInstanceOf(VersionedRequestInterface::class);
});

it('creates a versioned resource object', function () {
    $endpoint = new TestEndpoint();
    $model = mock(\MyParcelNL\Pdk\Base\Contract\Arrayable::class);

    $versionedResource = $endpoint->createVersionedResource($model, 1);

    expect($versionedResource)->toBeInstanceOf(VersionedResourceInterface::class);
});

it('validates a request successfully by default', function () {
    $endpoint = new TestEndpoint();
    $request = new Request();

    $isValid = $endpoint->validate($request);

    expect($isValid)->toBeTrue();
});

it('detects the version from Content-Type header', function () {
    $endpoint = new TestEndpoint();

    $request = new Request();
    $request->headers->set('Content-Type', 'application/json; version=2');

    $version = $endpoint->testDetectVersion($request);

    expect($version)->toBe(2);
});

it('extracts only the major version from the version string in header', function () {
    $endpoint = new TestEndpoint();

    $version = $endpoint->testExtractVersionFromHeader('application/json; version=v3.1.4-beta');

    expect($version)->toBe(3);
});

it('defaults to v1 when no version is specified in headers', function () {
    $endpoint = new TestEndpoint();

    $request = new Request();

    $version = $endpoint->testDetectVersion($request);

    expect($version)->toBe(1);
});

it('has a fallback to the accept header if no content-type header is present', function () {
    $endpoint = new TestEndpoint();

    $request = new Request();
    $request->headers->set('Accept', 'application/json; version=2');

    $version = $endpoint->testDetectVersion($request);

    expect($version)->toBe(2);
});

it('returns 406 Not Acceptable when an unsupported version is requested via Content-Type', function () {
    $endpoint = new TestEndpoint();
    $request = new Request();
    $request->headers->set('Content-Type', 'application/json; version=3');

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('type', null);
    expect($content)->toHaveKey('title', 'Not Acceptable');
    expect($content)->toHaveKey('status', 406);
    expect($content)->toHaveKey('detail');
    expect($content['detail'])->toContain('API version 3 is not supported');
    expect($content['detail'])->toContain('Supported versions: 1, 2');
});

it('returns 406 Not Acceptable when an unsupported version is requested via Accept header', function () {
    $endpoint = new TestEndpoint();
    $request = new Request();
    $request->headers->set('Accept', 'application/json; version=3');

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('type', null);
    expect($content)->toHaveKey('title', 'Not Acceptable');
    expect($content)->toHaveKey('status', 406);
    expect($content)->toHaveKey('detail');
    expect($content['detail'])->toContain('API version 3 is not supported');
    expect($content['detail'])->toContain('Supported versions: 1');
});

it('returns 406 Not Acceptable when default version is unsupported', function () {
    // Create endpoint that doesn't support version 1 (the default)
    $endpoint = new class extends AbstractEndpoint {
        public function getSupportedVersions(): array
        {
            return [2, 3]; // Only supports v2 and v3, not v1
        }

        protected function handleVersionedRequest(Request $request, int $version): Response
        {
            return new Response('test response');
        }

        public function createVersionedRequest(Request $request, int $version): VersionedRequestInterface
        {
            return mock(VersionedRequestInterface::class);
        }

        public function createVersionedResource($model, int $version): VersionedResourceInterface
        {
            return mock(VersionedResourceInterface::class);
        }
    };

    $request = new Request(); // No version header - should default to v1

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('status', 406);
    expect($content['detail'])->toContain('API version 1 is not supported');
    expect($content['detail'])->toContain('Supported versions: 2, 3');
});

it('Content-Type version takes precedence over Accept version when both are present', function () {
    $endpoint = new TestEndpoint(); // Only supports v1
    $request = new Request();
    $request->headers->set('Content-Type', 'application/json; version=4'); // Unsupported
    $request->headers->set('Accept', 'application/json; version=2'); // Supported

    $response = $endpoint->handle($request);

    // Should return 406 because Content-Type version (3) takes precedence and is unsupported
    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content['detail'])->toContain('API version 4 is not supported');
});
