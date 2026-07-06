<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule\ImplicationsService;
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
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    protected $pdkAccountRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    protected $pdkSettingsRepository;

    /**
     * @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository
     */
    protected CarrierCapabilitiesRepository $carrierCapabilitiesRepository;

    /**
     * @var \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService
     */
    protected CapabilitiesService $capabilitiesService;

    /**
     * @var \MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule\ImplicationsService
     */
    protected ImplicationsService $implicationsService;

    public function __construct(
        PdkSettingsRepositoryInterface     $pdkSettingsRepository,
        PdkAccountRepositoryInterface      $pdkAccountRepository,
        AccountRepository                  $accountRepository,
        CarrierCapabilitiesRepository      $carrierCapabilitiesRepository,
        CapabilitiesService                $capabilitiesService,
        ImplicationsService                $implicationsService
    ) {
        $this->pdkSettingsRepository          = $pdkSettingsRepository;
        $this->pdkAccountRepository           = $pdkAccountRepository;
        $this->accountRepository              = $accountRepository;
        $this->carrierCapabilitiesRepository  = $carrierCapabilitiesRepository;
        $this->capabilitiesService            = $capabilitiesService;
        $this->implicationsService            = $implicationsService;
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

        return Actions::execute(PdkBackendActions::FETCH_CONTEXT, [
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
    protected function setShopDefaultCarrier(Account $account): void
    {
        $shop = $account->shops->first();

        if (! $shop || ! $shop->id) {
            Logger::warning('Cannot fetch default carrier: no shop or shop id available');
            return;
        }

        $apiResult = $this->implicationsService->getDefaultCarrierName($shop->id);

        // Availability is checked against the in-memory $shop->carriers (just resolved from
        // contract definitions); the persisted account is one step behind at this point.
        if ($apiResult !== null && $shop->carriers->contains('carrier', $apiResult)) {
            $resolved = $apiResult;
        } else {
            // Carry forward so a single missing/unavailable implication does not wipe a good default.
            $previousAccount = $this->pdkAccountRepository->getAccount();
            $previousShop    = $previousAccount ? $previousAccount->shops->first() : null;
            $resolved        = $previousShop ? $previousShop->defaultCarrier : null;
        }

        if ($resolved !== null) {
            $shop->defaultCarrier = $resolved;
        }

        Logger::debug(sprintf('Shop default carrier set to %s', $shop->defaultCarrier ?? 'null'));
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    protected function setShopCarriers(Account $account): void
    {
        // The API key always resolves to exactly one shop, despite the collection shape.
        $shop           = $account->shops->first();
        $shop->carriers = $this->carrierCapabilitiesRepository->getContractDefinitions();
    }

    /**
     * Persist the API key's validity flag for {@see \MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository} to consult.
     * Re-evaluated whenever the user (re-)saves a key after a 401 surfaces in the backoffice.
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
        $existingSettings = $this->pdkSettingsRepository->all()->account;
        $mergedData       = array_merge($existingSettings->toArray(), $settings);
        $accountSettings  = new AccountSettings($mergedData);

        $this->pdkSettingsRepository->storeSettings($accountSettings);

        // SDK services capture the API key on their Configuration at construction (DI-time, before
        // this request stored the new key). Refresh each service that this request will still call.
        $this->capabilitiesService->refreshApiConfig();
        $this->implicationsService->refreshApiConfig();

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

        // Guards against legacy "remove API key" UI paths that surfaced as exceptions here.
        try {
            $account = $this->accountRepository->getAccount();
            Pdk::get(PropositionService::class)->setActivePropositionId($account->platformId);
        } catch (\Throwable $e) {
            Pdk::get(PropositionService::class)->clearActivePropositionId();
            $this->setApiKeyValidity(false);
            $this->pdkAccountRepository->store(null);
            throw $e;
        }

        $this->setShopCarriers($account);
        $this->setShopDefaultCarrier($account);
        $this->pdkAccountRepository->store($account);
        $this->setApiKeyValidity(true);
        Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);
    }
}
