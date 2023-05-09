<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Contract\ActionInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAccountAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository
     */
    private $carrierConfigurationRepository;

    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository
     */
    private $carrierOptionsRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface         $settingsRepository
     * @param  \MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface           $accountRepository
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository $carrierConfigurationRepository
     * @param  \MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository       $carrierOptionsRepository
     */
    public function __construct(
        SettingsRepositoryInterface        $settingsRepository,
        AccountRepositoryInterface         $accountRepository,
        ShopCarrierConfigurationRepository $carrierConfigurationRepository,
        ShopCarrierOptionsRepository       $carrierOptionsRepository
    ) {
        $this->settingsRepository             = $settingsRepository;
        $this->accountRepository              = $accountRepository;
        $this->carrierConfigurationRepository = $carrierConfigurationRepository;
        $this->carrierOptionsRepository       = $carrierOptionsRepository;
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

        $accountSettings = new AccountSettings($settings);

        $this->settingsRepository->storeSettings($accountSettings);

        $account = $accountSettings->apiKey
            ? $this->accountRepository->getAccount(true)
            : null;

        $this->updateAndSaveAccount($account);

        return Actions::execute(PdkSharedActions::FETCH_CONTEXT, ['context' => Context::ID_DYNAMIC]);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    protected function updateAndSaveAccount(?Account $account): void
    {
        if ($account) {
            $shop                  = $account->shops->first();
            $carrierConfigurations = $this->carrierConfigurationRepository->getCarrierConfigurations($shop->id);
            $carrierOptions        = $this->carrierOptionsRepository->getCarrierOptions($shop->id);

            $shop->carrierConfigurations = $carrierConfigurations->toArray();
            $shop->carrierOptions        = $carrierOptions->toArray();
        }

        $this->accountRepository->store($account);
    }
}
