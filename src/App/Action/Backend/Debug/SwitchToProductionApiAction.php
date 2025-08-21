<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchToProductionApiAction implements ActionInterface
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
            // Remove the acceptance API URL from the cache file
            $cacheFile = sys_get_temp_dir() . \MyParcelNL\Pdk\Base\Config::ACCEPTANCE_CACHE_FILE;
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }

            // Switch the base URL back to the production API for the current session
            $this->apiService->setBaseUrl('https://api.myparcel.nl');

            // Remove the API key because production needs its own API key
            // Use the same method as DeleteAccountAction
            $this->updateAccountSettings([]);

            Logger::info('API URL successfully switched back to production environment and API key removed');

            Notifications::success(
                'API URL successfully switched back to production environment. API key has been removed - please enter your production API key.',
                [],
                Notification::CATEGORY_GENERAL
            );

            // Call UPDATE_ACCOUNT to reload the context, just like DeleteAccountAction
            return Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);
        } catch (\Throwable $e) {
            Logger::error('Failed to switch API URL back to production environment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notifications::error(
                'Failed to switch API URL back to production environment',
                [],
                Notification::CATEGORY_GENERAL
            );

            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to switch API URL back to production environment: ' . $e->getMessage(),
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
