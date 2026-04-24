<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
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
        $this->normalizeCarrierSettings($settings);

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
        if ($settings->carrier === null) {
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

    /**
     * Normalize carrier export settings through the existing carrier calculators so the
     * saved defaults cannot drift away from the effective shipment rules used elsewhere.
     *
     * Example: PostNL age check requires signature + only recipient, while UPS age check
     * only requires signature. The settings page should persist those same rules.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
     */
    private function normalizeCarrierSettings(Settings $settings): void
    {
        if ($settings->carrier === null) {
            return;
        }

        /** @var CarrierRepositoryInterface $carrierRepository */
        $carrierRepository = Pdk::get(CarrierRepositoryInterface::class);

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        $localCountryCode = Pdk::get(PropositionService::class)->getPropositionConfig()->countryCode
            ?: CountryCodes::CC_NL;

        foreach ($settings->carrier as $carrierName => $carrierSettings) {
            if (
                ! $carrierSettings instanceof CarrierSettings
                || ! is_string($carrierName)
                || CarrierSettings::ID === $carrierName
                || SettingsManager::KEY_ALL === $carrierName
            ) {
                continue;
            }

            $carrier = $carrierRepository->find($carrierName);

            if (! $carrier) {
                try {
                    $carrier = $carrierRepository->findByLegacyName(strtolower($carrierName));
                } catch (InvalidArgumentException $e) {
                    continue;
                }
            }

            if (! $carrier) {
                continue;
            }

            $order = new PdkOrder([
                'deliveryOptions' => new DeliveryOptions([
                    'carrier'         => $carrier,
                    'shipmentOptions' => new ShipmentOptions(
                        $this->mapCarrierSettingsToShipmentOptions($carrierSettings, $definitions)
                    ),
                ]),
                'shippingAddress' => new ShippingAddress([
                    'cc' => $localCountryCode,
                ]),
            ]);

            (new CarrierSpecificCalculator($order))->calculate();

            $this->mapShipmentOptionsBackToCarrierSettings(
                $carrierSettings,
                $order->deliveryOptions->shipmentOptions,
                $definitions
            );
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings                      $carrierSettings
     * @param  array<array-key, OrderOptionDefinitionInterface>                    $definitions
     *
     * @return array<string, mixed>
     */
    private function mapCarrierSettingsToShipmentOptions(
        CarrierSettings $carrierSettings,
        array $definitions
    ): array {
        $shipmentOptions = [];

        foreach ($definitions as $definition) {
            $carrierSettingsKey = $definition->getCarrierSettingsKey();
            $shipmentOptionsKey = $definition->getShipmentOptionsKey();

            if (! $carrierSettingsKey || ! $shipmentOptionsKey) {
                continue;
            }

            $shipmentOptions[$shipmentOptionsKey] = $carrierSettings->getAttribute($carrierSettingsKey);
        }

        return $shipmentOptions;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings                      $carrierSettings
     * @param  \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions                      $shipmentOptions
     * @param  array<array-key, OrderOptionDefinitionInterface>                    $definitions
     *
     * @return void
     */
    private function mapShipmentOptionsBackToCarrierSettings(
        CarrierSettings $carrierSettings,
        ShipmentOptions $shipmentOptions,
        array $definitions
    ): void {
        foreach ($definitions as $definition) {
            $carrierSettingsKey = $definition->getCarrierSettingsKey();
            $shipmentOptionsKey = $definition->getShipmentOptionsKey();

            if (! $carrierSettingsKey || ! $shipmentOptionsKey) {
                continue;
            }

            $carrierSettings->setAttribute(
                $carrierSettingsKey,
                $shipmentOptions->getAttribute($shipmentOptionsKey)
            );
        }
    }
}
