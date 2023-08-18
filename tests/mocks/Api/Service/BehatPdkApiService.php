<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Adapter\BehatPdkClientAdapter;

final class BehatPdkApiService extends AbstractApiService
{
    /**
     * @param  \MyParcelNL\Pdk\Api\Adapter\BehatPdkClientAdapter $clientAdapter
     */
    public function __construct(BehatPdkClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);
    }
}
