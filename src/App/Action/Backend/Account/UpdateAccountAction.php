<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAccountAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepository
     */
    protected $accountRepository;

    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository
     */
    protected $carrierConfigurationRepository;

    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository
     */
    protected $carrierOptionsRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    protected $pdkAccountRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    protected $pdkSettingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository $carrierConfigurationRepository
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository       $carrierOptionsRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface      $pdkSettingsRepository
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface    $pdkAccountRepository
     */
    public function __construct(
        ShopCarrierConfigurationRepository $carrierConfigurationRepository,
        ShopCarrierOptionsRepository       $carrierOptionsRepository,
        PdkSettingsRepositoryInterface     $pdkSettingsRepository,
        PdkAccountRepositoryInterface      $pdkAccountRepository,
        AccountRepository                  $accountRepository
    ) {
        $this->carrierConfigurationRepository = $carrierConfigurationRepository;
        $this->carrierOptionsRepository       = $carrierOptionsRepository;
        $this->pdkSettingsRepository          = $pdkSettingsRepository;
        $this->pdkAccountRepository           = $pdkAccountRepository;
        $this->accountRepository              = $accountRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {
        $body     = json_decode($request->getContent(), true);
        $settings = $body['data']['account_settings'] ?? [];

        $accountSettings = $this->updateAccountSettings($settings);

        $this->updateAndSaveAccount($accountSettings);

        return Actions::execute(PdkSharedActions::FETCH_CONTEXT, [
            'context' => implode(',', [
                Context::ID_DYNAMIC,
                Context::ID_PLUGIN_SETTINGS_VIEW,
            ]),
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    protected function fillAccount(Account $account): void
    {
        $shop = $account->shops->first();

        $shop->carrierConfigurations = $this->carrierConfigurationRepository->getCarrierConfigurations($shop->id);
        $shop->carriers              = $this->carrierOptionsRepository->getCarrierOptions($shop->id);
    }

    /**
     * Stores the validity of the api key in the account, for use in
     * src/App/Account/Repository/AbstractPdkAccountRepository.php
     * When the user rotates the key in the backoffice, the plugin will start receiving 401 responses from the API,
     * however for the purpose of the PDK the api key is still valid. Upon receiving the errors the user will no doubt
     * update their api key in the settings at which point the validity is reassessed here (updateAndSaveAccount).
     *
     * @param  bool $apiKeyIsValid
     *
     * @return void
     */
    protected function setApiKeyValidity(bool $apiKeyIsValid): void
    {
        $accountSettings = $this->pdkSettingsRepository->all()->account
            ->fill([
                AccountSettings::API_KEY_VALID => $apiKeyIsValid,
            ]);

        $this->pdkSettingsRepository->storeSettings($accountSettings);
    }

    /**
     * @param  array $settings
     *
     * @return \MyParcelNL\Pdk\Settings\Model\AccountSettings
     */
    protected function updateAccountSettings(array $settings): AccountSettings
    {
        // Get existing account settings to preserve them
        $existingSettings = $this->pdkSettingsRepository->all()->account;

        // Always merge with existing settings
        $existingData    = $existingSettings->toArray();
        $mergedData      = array_merge($existingData, $settings);
        $accountSettings = new AccountSettings($mergedData);

        $this->pdkSettingsRepository->storeSettings($accountSettings);

        return $accountSettings;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AccountSettings $accountSettings
     *
     * @return void
     * @throws \Throwable
     */
    protected function updateAndSaveAccount(AccountSettings $accountSettings): void
    {
        if (! $accountSettings->apiKey) {
            $this->setApiKeyValidity(false);
            $this->pdkAccountRepository->store(null);
            return;
        }

        // this try is for the case when the UI is no longer supplying an empty api key when you click "remove api key"
        try {
            $account = $this->accountRepository->getAccount();
        } catch (\Throwable $e) {
            $this->setApiKeyValidity(false);
            $this->pdkAccountRepository->store(null);
            throw $e;
        }

        $this->fillAccount($account);
        $this->pdkAccountRepository->store($account);
        $this->setApiKeyValidity(true);
        Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);
    }
}
