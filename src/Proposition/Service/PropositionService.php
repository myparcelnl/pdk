<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Platform\PlatformManager;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Str;
use RuntimeException;

class PropositionService
{
    /**
     * Get the active proposition name.
     * This is used to fetch the proposition config.
     *
     * @return string
     */
    public function getActivePropositionName(): string
    {
        return Pdk::get(PlatformManager::class)->getPlatform();
    }

    /**
     * Get the proposition config.
     *
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfig(): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $propositionName = $this->getActivePropositionName();
        // @todo basic static caching implementation
        // @todo also add DB storage at some point
        return $this->fetchPropositionConfig($propositionName);
    }

    /**
     * Fetch the proposition config based on the platform name.
     * @param string $propositionName
     * @return PropositionConfig
     */
    public function fetchPropositionConfig(string $propositionName): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $url = null;
        $filePath = null;
        $configData = null;
        switch ($propositionName) {
            // @todo enable MyParcel / SendMyParcel URLs when this PR is merged:
            // https://github.com/mypadev/assets/pull/25

            // case Platform::MYPARCEL_NAME:
            // case Platform::LEGACY_MYPARCEL_NAME:
            //     // $url = 'https://assets.myparcel.nl/config/proposition.json';
            //     break;
            // case Platform::SENDMYPARCEL_NAME:
            // case Platform::LEGACY_SENDMYPARCEL_NAME:
            // $url = 'https://assets.sendmyparcel.be/config/proposition.json';
            // break;
            case Platform::FLESPAKKET_NAME:
                // @todo: remove flespakket in next major version
                Logger::deprecated('Flespakket platform is deprecated, please use MyParcel or SendMyParcel instead.');
                break;
            default:
                $filePath = __DIR__ . '/../../../config/proposition/' . $propositionName . '.json';
                break;
        }

        if ($url) {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(sprintf('Failed to fetch proposition config from URL: %s', $url));
            }
            $configData = $response->getBody()->getContents();
        }

        if ($filePath) {
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException(sprintf('Proposition config name %s does not exist', $propositionName));
            }
            $configData = file_get_contents($filePath);
        }

        $configArray = json_decode($configData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('Invalid JSON in proposition config file: %s %s', $filePath, $url));
        }

        // Create a PropositionConfig instance from the array
        $propositionConfig = new PropositionConfig($configArray);
        return $propositionConfig;

    }

    /**
     * Get the carriers from the proposition config as CarrierCollection.
     * @param $legacyFormat Whether to return the carriers in the legacy format (lowercase) or the new format (SCREAMING_SNAKE_CASE).
     *           Legacy = "postnl", "dhlparcelconnect", "bpost", etc.
     *           New = "POSTNL", "DHL_PARCEL_CONNECT", "BPOST", etc.
     * @return CarrierCollection
     */
    public function getCarriers(): \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
    {
        $carrierModels = [];
        foreach ($this->getPropositionConfig()->contracts->available as $contract) {
            $carrierData = [
                'name' => $contract['carrier']['name'],
                'id' => $contract['carrier']['id'],
                'contractId' => $contract['id'] ?? null,
                'outboundFeatures' => $contract['outboundFeatures'] ?? [],
                'inboundFeatures' => $contract['inboundFeatures'] ?? [],
            ];
            $carrierModels[] = new Carrier($carrierData);
        }

        // Combine with carrier-specific own contracts
        foreach ($this->getPropositionConfig()->contracts->availableForCustomCredentials as $customContract) {
            // Skip already-defined carriers
            if (in_array($customContract['carrier']['id'], array_column($carrierModels, 'id'))) {
                continue;
            }
            $carrierData = [
                'name' => $customContract['carrier']['name'],
                'id' => $customContract['carrier']['id'],
                'contractId' => $customContract['id'] ?? null,
                'type' => Carrier::TYPE_CUSTOM
            ];
            $carrierModels[] = new Carrier($carrierData);
        }
        return new CarrierCollection($carrierModels);
    }

    /**
     * Get the default carrier from the proposition config.
     * Returns the outbound carrier by default. Use $outbound = false to get the inbound (return shipments) carrier.

     * @param bool $outbound
     * @return null|Carrier
     */
    public function getDefaultCarrier($outbound = true): Carrier
    {
        if ($outbound) {
            $defaultCarrierId = $this->getPropositionConfig()->contracts->outbound['default']['carrier']['id'];
        } else {
            $defaultCarrierId = $this->getPropositionConfig()->contracts->inbound['default']['carrier']['id'];
        }
        return $this->getCarriers()->where('id', $defaultCarrierId)->first();
    }

    /**
     * Map the proposition config to the platform config for backwards compatibility.
     * This maps to existing Platform config keys, but does not transform the values.
     *
     * @param PropositionConfig $propositionConfig
     * @return array
     * @throws RuntimeException
     */
    public function mapToPlatformConfig(PropositionConfig $propositionConfig): array
    {
        return [
            'name' => $propositionConfig->proposition->key,
            'human' => $propositionConfig->proposition->name,
            'backofficeUrl' => $propositionConfig->applications['backoffice']['url'] ?? null,
            'supportUrl' => $propositionConfig->applications['developerPortal']['url'] ?? null,
            'localCountry' => $propositionConfig->countryCode,
            'defaultCarrier' => $this->getDefaultCarrier()->name,
            'defaultCarrierId' => $this->getDefaultCarrier()->id,
            'defaultSettings' => [], // @todo no longer supported, remove in v3.0.0
            'carriers' => $this->getCarriers()->toArray(), // @optional conversion?
        ];
    }

    /**
     * Given SCREAMING_SNAKE_CASE package name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $packageType a package type definition from the Proposition config
     * @return string|false a package type definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function packageTypeNameForDeliveryOptions(string $packageType)
    {
        $supportedTypes = DeliveryOptions::PACKAGE_TYPES_NAMES;
        $converted = strtolower($packageType);

        return in_array($converted, $supportedTypes, true) ? $converted : false;
    }

    /**
     * Given SCREAMING_SNAKE_CASE delivery type name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $deliveryType a delivery type definition from the Proposition config
     * @return string|false a delivery type definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function deliveryTypeNameForDeliveryOptions(string $deliveryType)
    {
        $supportedTypes = DeliveryOptions::DELIVERY_TYPES_NAMES;
        $converted = strtolower(str_replace('_DELIVERY', '', $deliveryType));

        return in_array($converted, $supportedTypes, true) ? $converted : false;
    }

    /**
     * Given SCREAMING_SNAKE_CASE shipment option, return the camelCase version for delivery options if defined in that class.
     *
     * @param string $shipmentOption a shipment option definition from the Proposition config
     * @return string a shipment option definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function shipmentOptionNameForDeliveryOptions(string $shipmentOption)
    {
        return Str::camel(strtolower($shipmentOption));
    }
}
