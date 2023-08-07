<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use function MyParcelNL\Pdk\Tests\mockPlatform;

/**
 * This context is for tests that use the settings repository.
 */
final class SettingsContext extends AbstractContext
{
    /**
     * @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository
     */
    protected $settingsRepository;

    /**
     * @param  null|string $name
     * @param  array       $data
     * @param  string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

        $this->onAfterScenario(function () {
            /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $accountRepository */
            $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);

            // Delete any account that was created during the test.
            $accountRepository->store(null);

            // Wipe the settings repository.
            $this->settingsRepository->reset();
        });
    }

    /**
     * @Given I am a platform :platform user
     */
    public function IAmAPlatformUser(string $platform): void
    {
        $resetPlatform = mockPlatform($platform);

        $this->onAfterScenario(function () use ($resetPlatform) {
            $resetPlatform();
        });
    }

    /**
     * @Given a valid API key is set
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function aValidAPIKeyIsSet(): void
    {
        $platform = Platform::getPlatform();

        $apiKey = getenv('API_KEY_' . strtoupper($platform)) ?? 'valid-api-key';

        $this->settingsRepository->storeSettings(new AccountSettings([AccountSettings::API_KEY => $apiKey]));
    }

    /**
     * @Given an invalid API key is set
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function anInvalidAPIKeyIsSet(): void
    {
        $this->settingsRepository->storeSettings(new AccountSettings([AccountSettings::API_KEY => 'invalid-api-key']));
    }
}
