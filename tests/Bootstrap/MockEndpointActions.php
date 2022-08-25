<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Action\PdkEndpointActions;

class MockEndpointActions extends PdkEndpointActions
{
    public function getBaseUrl(): string
    {
        return 'CMS_URL';
    }
}
