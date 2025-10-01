<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteAccountAction extends UpdateAccountAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        // Explicitly delete all account settings, including environment
        $this->pdkSettingsRepository->storeSettings(new AccountSettings([
            'id' => AccountSettings::ID,
            'apiKey' => null,
            'apiKeyValid' => false,
            'environment' => null
        ]));

        return Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);
    }
}
