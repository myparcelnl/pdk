<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAccountAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository
     */
    private $carrierConfigurationRepository;

    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository
     */
    private $carrierOptionsRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $pdkAccountRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    private $pdkSettingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository $carrierConfigurationRepository
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository       $carrierOptionsRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface         $pdkSettingsRepository
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface    $pdkAccountRepository
     */
    public function __construct(
        ShopCarrierConfigurationRepository $carrierConfigurationRepository,
        ShopCarrierOptionsRepository       $carrierOptionsRepository,
        SettingsRepositoryInterface        $pdkSettingsRepository,
        PdkAccountRepositoryInterface      $pdkAccountRepository
    ) {
        $this->carrierConfigurationRepository = $carrierConfigurationRepository;
        $this->carrierOptionsRepository       = $carrierOptionsRepository;
        $this->pdkSettingsRepository          = $pdkSettingsRepository;
        $this->pdkAccountRepository           = $pdkAccountRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $body     = json_decode($request->getContent(), true);
        $settings = $body['data']['account_settings'] ?? [];

        $accountSettings = $this->updateAccountSettings($settings);

        $this->updateAndSaveAccount($accountSettings);

        return Actions::execute(PdkSharedActions::FETCH_CONTEXT, ['context' => Context::ID_DYNAMIC]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    protected function fillAccount(Account $account): void
    {
        $shop = $account->shops->first();

        $carrierConfigurations       = $this->carrierConfigurationRepository->getCarrierConfigurations($shop->id);
        $shop->carrierConfigurations = $carrierConfigurations->toArray();

        $carrierOptions       = $this->carrierOptionsRepository->getCarrierOptions($shop->id);
        $shop->carrierOptions = $carrierOptions->toArray();
    }

    /**
     * @param $settings
     *
     * @return \MyParcelNL\Pdk\Settings\Model\AccountSettings
     */
    protected function updateAccountSettings($settings): AccountSettings
    {
        $accountSettings = new AccountSettings($settings);

        $this->pdkSettingsRepository->storeSettings($accountSettings);

        return $accountSettings;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AccountSettings $accountSettings
     *
     * @return void
     */
    protected function updateAndSaveAccount(AccountSettings $accountSettings): void
    {
        $foundAccount = $accountSettings->apiKey
            ? $this->pdkAccountRepository->getAccount(true)
            : null;

        if ($foundAccount) {
            $this->fillAccount($foundAccount);
        }

        $this->pdkAccountRepository->store($foundAccount);
    }
}
