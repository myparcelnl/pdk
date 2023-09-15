<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Api\Service;

use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Integration\Api\Adapter\BehatMyParcelClientAdapter;

final class BehatMyParcelApiService extends MyParcelApiService
{
    public function __construct(BehatMyParcelClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);
    }

    public function getHeaders(): array
    {
        return array_replace(parent::getHeaders(), [
            'X-Api-Key' => Settings::get(AccountSettings::API_KEY, AccountSettings::ID),
        ]);
    }
}

