<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request;
use Symfony\Component\HttpFoundation\Request;

it('returns version 1 as integer', function () {
    expect(GetDeliveryOptionsV1Request::getVersion())->toBe(1);
    expect(GetDeliveryOptionsV1Request::getVersion())->toBeInt();
});
