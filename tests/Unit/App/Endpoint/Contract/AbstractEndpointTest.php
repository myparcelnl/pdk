<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
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
    public function handle(Request $request): Response
    {
        return new Response('test response');
    }

    protected function createVersionedRequest(Request $request, string $version): VersionedRequestInterface
    {
        return mock(VersionedRequestInterface::class);
    }

    protected function createVersionedResourceResponse(array $data, string $version): VersionedResourceInterface
    {
        return mock(VersionedResourceInterface::class);
    }

    // Expose protected methods for testing
    public function testGetRequestBody(Request $request): array
    {
        return $this->getRequestBody($request);
    }

    public function testDetectVersion(Request $request): string
    {
        return $this->detectVersion($request);
    }

    public function testExtractVersionFromHeader(string $header): ?string
    {
        return $this->extractVersionFromHeader($header);
    }
}

describe('AbstractEndpoint', function () {
    beforeEach(function () {
        $this->endpoint = new TestEndpoint();
    });

    describe('validate()', function () {
        it('returns true by default', function () {
            $request = new Request();

            expect($this->endpoint->validate($request))->toBe(true);
        });
    });

    describe('getRequestBody()', function () {
        it('returns empty array for empty request content', function () {
            $request = new Request();

            $result = $this->endpoint->testGetRequestBody($request);

            expect($result)->toBe([]);
        });

        it('returns parsed JSON array from request content', function () {
            $data = ['key' => 'value', 'number' => 123];
            $request = new Request([], [], [], [], [], [], json_encode($data));

            $result = $this->endpoint->testGetRequestBody($request);

            expect($result)->toBe($data);
        });

        it('returns empty array for invalid JSON', function () {
            $request = new Request([], [], [], [], [], [], 'invalid json');

            $result = $this->endpoint->testGetRequestBody($request);

            expect($result)->toBe([]);
        });

        it('returns empty array for non-array JSON', function () {
            $request = new Request([], [], [], [], [], [], '"string value"');

            $result = $this->endpoint->testGetRequestBody($request);

            expect($result)->toBe([]);
        });
    });

    describe('detectVersion()', function () {
        it('returns version from Content-Type header when present', function () {
            $request = new Request();
            $request->headers->set('Content-Type', 'application/json; version=2');
            $request->headers->set('Accept', 'application/json; version=1');

            $result = $this->endpoint->testDetectVersion($request);

            expect($result)->toBe('2');
        });

        it('returns version from Accept header when Content-Type has no version', function () {
            $request = new Request();
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('Accept', 'application/json; version=3');

            $result = $this->endpoint->testDetectVersion($request);

            expect($result)->toBe('3');
        });

        it('returns default version 1 when no version headers present', function () {
            $request = new Request();
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('Accept', 'application/json');

            $result = $this->endpoint->testDetectVersion($request);

            expect($result)->toBe('1');
        });

        it('returns default version 1 when no headers present at all', function () {
            $request = new Request();

            $result = $this->endpoint->testDetectVersion($request);

            expect($result)->toBe('1');
        });

        it('prioritizes Content-Type over Accept header per ADR-0011', function () {
            $request = new Request();
            $request->headers->set('Content-Type', 'application/json; version=5');
            $request->headers->set('Accept', 'application/json; version=4');

            $result = $this->endpoint->testDetectVersion($request);

            expect($result)->toBe('5');
        });
    });

    describe('extractVersionFromHeader()', function () {
        it('extracts major version from simple version parameter', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=2');

            expect($result)->toBe('2');
        });

        it('extracts major version from semantic version', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=1.0.0');

            expect($result)->toBe('1');
        });

        it('extracts major version from version with v prefix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=v2.1.3');

            expect($result)->toBe('2');
        });

        it('extracts major version from version with beta suffix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=3.0-beta.1');

            expect($result)->toBe('3');
        });

        it('extracts major version from version with v prefix and beta suffix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=v1.2-alpha');

            expect($result)->toBe('1');
        });

        it('extracts version from header with multiple parameters', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; charset=utf-8; version=v4.1.0; boundary=something');

            expect($result)->toBe('4');
        });

        it('returns null when no version parameter present', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json');

            expect($result)->toBe(null);
        });

        it('returns null for empty header', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('');

            expect($result)->toBe(null);
        });

        it('extracts version with comma-separated values', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=v5.0, text/plain');

            expect($result)->toBe('5');
        });

        it('rejects invalid version with non-v prefix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=x1.0');

            expect($result)->toBe(null);
        });

        it('rejects version starting with non-digit after v', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=va1.0');

            expect($result)->toBe(null);
        });

        it('rejects version with only letters', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=stable');

            expect($result)->toBe(null);
        });

        it('extracts multi-digit major version', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=12.5.0');

            expect($result)->toBe('12');
        });

        it('extracts version zero', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=0.9.1');

            expect($result)->toBe('0');
        });

        it('handles version with rc suffix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=2.0-rc1');

            expect($result)->toBe('2');
        });

        it('handles version with snapshot suffix', function () {
            $result = $this->endpoint->testExtractVersionFromHeader('application/json; version=v3.1.0-SNAPSHOT');

            expect($result)->toBe('3');
        });
    });

    describe('abstract methods', function () {
        it('requires handle method implementation', function () {
            $request = new Request();

            $response = $this->endpoint->handle($request);

            expect($response)->toBeInstanceOf(Response::class);
            expect($response->getContent())->toBe('test response');
        });

        it('requires createVersionedRequest method implementation', function () {
            $request = new Request();

            $versionedRequest = $this->endpoint->createVersionedRequest($request, '1');

            expect($versionedRequest)->toBeInstanceOf(VersionedRequestInterface::class);
        });

        it('requires createVersionedResourceResponse method implementation', function () {
            $data = ['key' => 'value'];

            $versionedResource = $this->endpoint->createVersionedResourceResponse($data, '1');

            expect($versionedResource)->toBeInstanceOf(VersionedResourceInterface::class);
        });
    });
});
