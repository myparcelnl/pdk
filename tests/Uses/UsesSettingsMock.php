<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;

final class UsesSettingsMock implements BaseMock
{
    public function afterEach(): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $settingsRepository */
        $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

        $settingsRepository->reset();
    }
}
