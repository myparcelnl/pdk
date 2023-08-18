<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Adapter\BehatMyParcelClientAdapter;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

final class BehatMyParcelApiService extends MyParcelApiService
{
    /**
     * @param  \MyParcelNL\Pdk\Api\Adapter\BehatMyParcelClientAdapter $clientAdapter
     */
    public function __construct(BehatMyParcelClientAdapter $clientAdapter)
    {
        parent::__construct($clientAdapter);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return array_replace(parent::getHeaders(), [
            'X-Api-Key' => Settings::get(AccountSettings::API_KEY, AccountSettings::ID),
        ]);
    }
}

