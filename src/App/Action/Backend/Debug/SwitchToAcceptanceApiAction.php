<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SwitchToAcceptanceApiAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface
     */
    private $apiService;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface                 $apiService
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     */
    public function __construct(
        ApiServiceInterface            $apiService,
        PdkSettingsRepositoryInterface $settingsRepository
    ) {
        $this->apiService         = $apiService;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        try {
            // Switch the base URL to the acceptance API for the current session
            $this->apiService->setBaseUrl(Config::API_URL_ACCEPTANCE);

            // Store the acceptance API URL in a file for persistence (backward compatibility)
            $acceptanceUrl = Config::API_URL_ACCEPTANCE;
            $cacheFile = sys_get_temp_dir() . Config::ACCEPTANCE_CACHE_FILE;
            file_put_contents($cacheFile, $acceptanceUrl);

            // Update account settings to store the environment preference
            $this->updateAccountSettings(['environment' => 'acceptance']);

            Logger::info('API URLs successfully switched to acceptance environment');

            Notifications::success(
                'API URLs successfully switched to acceptance environment.',
                [],
                Notification::CATEGORY_GENERAL
            );

            // Call UPDATE_ACCOUNT to reload the context, just like DeleteAccountAction
            return Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);
        } catch (Throwable $e) {
            Logger::error('Failed to switch API URLs to acceptance environment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notifications::error(
                'Failed to switch API URLs to acceptance environment',
                [],
                Notification::CATEGORY_GENERAL
            );

            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to switch API URLs to acceptance environment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  array $settings
     *
     * @return \MyParcelNL\Pdk\Settings\Model\AccountSettings
     */
    protected function updateAccountSettings(array $settings): AccountSettings
    {
        $accountSettings = new AccountSettings($settings);

        $this->settingsRepository->storeSettings($accountSettings);

        return $accountSettings;
    }
}
