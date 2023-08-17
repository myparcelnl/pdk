<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of AccountSettings
 * @method AccountSettings make()
 * @method $this withApiKey(string $apiKey)
 * @method $this withApiKeyValid(bool $apiKeyValid)
 */
final class AccountSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return AccountSettings::class;
    }
}
