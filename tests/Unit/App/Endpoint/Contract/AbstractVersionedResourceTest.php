<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\Base\Model\Model;
use Symfony\Component\HttpFoundation\Request;

it('returns Accept header with resource version when no supported versions passed', function () {
    $model    = new class extends Model {};
    $resource = new class($model) extends AbstractVersionedResource {
        public static function getVersion(): int
        {
            return 1;
        }
    };

    $response = $resource->createResponse(new Request());

    expect($response->headers->get('Accept'))->toBe('application/json; version=1');
});

it('returns Accept header with single supported version', function () {
    $model    = new class extends Model {};
    $resource = new class($model) extends AbstractVersionedResource {
        public static function getVersion(): int
        {
            return 1;
        }
    };

    $response = $resource->createResponse(new Request(), 200, [1]);

    expect($response->headers->get('Accept'))->toBe('application/json; version=1');
});

it('returns Accept header with multiple supported versions', function () {
    $model    = new class extends Model {};
    $resource = new class($model) extends AbstractVersionedResource {
        public static function getVersion(): int
        {
            return 1;
        }
    };

    $response = $resource->createResponse(new Request(), 200, [1, 2]);

    expect($response->headers->get('Accept'))->toBe('application/json; version=1; version=2');
});

it('returns Accept header with three supported versions', function () {
    $model    = new class extends Model {};
    $resource = new class($model) extends AbstractVersionedResource {
        public static function getVersion(): int
        {
            return 2;
        }
    };

    $response = $resource->createResponse(new Request(), 200, [1, 2, 3]);

    expect($response->headers->get('Accept'))->toBe('application/json; version=1; version=2; version=3');
});

it('sets Content-Type header with resource version regardless of supported versions', function () {
    $model    = new class extends Model {};
    $resource = new class($model) extends AbstractVersionedResource {
        public static function getVersion(): int
        {
            return 2;
        }
    };

    $response = $resource->createResponse(new Request(), 200, [1, 2, 3]);

    expect($response->headers->get('Content-Type'))->toBe('application/json; version=2');
});
