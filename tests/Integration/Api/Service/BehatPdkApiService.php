<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Api\Service;

use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Tests\Integration\Api\Adapter\BehatPdkClientAdapter;

final class BehatPdkApiService extends AbstractApiService
{
    public function __construct(BehatPdkClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);
    }
}
