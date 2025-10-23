<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdatePluginSettingsAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService
     */
    private $deliveryOptionsResetService;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService          $deliveryOptionsResetService
     */
    public function __construct(
        PdkSettingsRepositoryInterface $settingsRepository,
        DeliveryOptionsResetService $deliveryOptionsResetService
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->deliveryOptionsResetService = $deliveryOptionsResetService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $body           = json_decode($request->getContent(), true);
        $pluginSettings = $body['data']['plugin_settings'] ?? [];

        if (empty($pluginSettings)) {
            throw new InvalidArgumentException('Request body is empty');
        }

        $settings = new Settings($pluginSettings);

        // Reset underlying delivery options settings when DELIVERY_OPTIONS_ENABLED is disabled
        $this->resetDeliveryOptionsWhenDisabled($settings);

        foreach (array_keys($pluginSettings) as $editedSettingsId) {
            $this->settingsRepository->storeSettings($settings->getAttribute($editedSettingsId));
        }

        return new JsonResponse([
            'plugin_settings' => $settings->toArrayWithoutNull(),
        ]);
    }

    /**
     * Reset all underlying delivery options settings to false when DELIVERY_OPTIONS_ENABLED is disabled.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
     */
    private function resetDeliveryOptionsWhenDisabled(Settings $settings): void
    {
        if (! $settings->carrier) {
            return;
        }

        foreach ($settings->carrier as $carrierName => $carrierSettings) {
            if (! $carrierSettings instanceof CarrierSettings) {
                continue;
            }

            // Check if DELIVERY_OPTIONS_ENABLED is being disabled
            if ($carrierSettings->deliveryOptionsEnabled === false) {
                $this->deliveryOptionsResetService->resetDeliveryOptions($carrierSettings);
            }
        }
    }
}
