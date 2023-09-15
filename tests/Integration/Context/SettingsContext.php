<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use function MyParcelNL\Pdk\Tests\factory;
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
     */
    public function aValidAPIKeyIsSet(): void
    {
        TestBootstrapper::hasApiKey($this->getValidApiKey());
    }

    /**
     * @Given an invalid API key is set
     */
    public function anInvalidAPIKeyIsSet(): void
    {
        TestBootstrapper::hasApiKey('invalid-api-key');
    }

    /**
     * @Given I expect the API key to be marked as invalid
     */
    public function iExpectTheAPIKeyToBeMarkedAsInvalid(): void
    {
        $this->assertApiKeyValidity(false);
    }

    /**
     * @Given I expect the API key to be marked as valid
     */
    public function iExpectTheAPIKeyToBeMarkedAsValid(): void
    {
        $this->assertApiKeyValidity(true);
    }

    /**
     * @Given the :category setting :setting is :value
     */
    public function theSettingIsValue(string $category, string $setting, $value): void
    {
        $resolvedValue = $this->resolveSettingValue($value);

        factory(\MyParcelNL\Pdk\Settings\Model\Settings::class)
            ->with([
                $category => [
                    $setting => $resolvedValue,
                ],
            ])
            ->store();
    }

    protected function assertApiKeyValidity(bool $valid): void
    {
        $isValid = Settings::get(AccountSettings::API_KEY_VALID, AccountSettings::ID);

        if ($valid) {
            self::assertTrue($isValid, 'API key is not marked as valid');
            return;
        }

        self::assertFalse($isValid, 'API key is not marked as invalid');
    }

    /**
     * @return mixed
     */
    private function resolveSettingValue(mixed $value)
    {
        return match ($value) {
            'enabled' => true,
            'disabled' => false,
            default => $value,
        };
    }
}
