<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;

class PropositionService
{
    public function __construct()
    {
    }

    /**
     * Get the active proposition name.
     * This is used to fetch the proposition config.
     *
     * @return string
     */
    public function getActivePropositionName(): string
    {
        // This should return the active proposition name based on the platform or context.
        // For now, we will return a hardcoded value for testing purposes.
        return 'myparcel'; // @todo This should be replaced with the actual logic to get the active proposition
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
        // $assetsClient = new Client();
        // Fetch the proposition config from a json file
        // open config/propositions/{propositionName}.json

        /**
         * @todo temporary testing code --- IGNORE ---
         * This should be replaced with a proper implementation that fetches the config from assets.
         */
        $filePath = __DIR__ . '/../../../config/proposition/' . $propositionName . '.json';
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Proposition config file not found: %s', $filePath));
        }
        $configData = file_get_contents($filePath);
        if ($configData === false) {
            throw new \RuntimeException(sprintf('Failed to read proposition config file: %s', $filePath));
        }
        $configArray = json_decode($configData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('Invalid JSON in proposition config file: %s', $filePath));
        }
        // Create a PropositionConfig instance from the array
        $propositionConfig = new PropositionConfig($configArray);
        return $propositionConfig;

    }

    /**
     * Get the carriers from the proposition config as CarrierCollection.
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
                'capabilities' => $contract['outboundFeatures'] ?? [],
                'returnCapabilities' => $contract['inboundFeatures'] ?? [],
                'features' => $contract['metadata'] ?? [],
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
            $defaultContractId = $this->getPropositionConfig()->contracts->outbound['default']['id'];
        } else {
            $defaultContractId = $this->getPropositionConfig()->contracts->inbound['default']['id'];
        }
        return $this->getCarriers()->where('contractId', $defaultContractId)->first();
    }

    public function mapToPlatformConfig(PropositionConfig $propositionConfig): array
    {
        // Map the proposition config to the platform config
        return [
            'name' => $propositionConfig->proposition->key,
            'human' => $propositionConfig->proposition->name,
            'backofficeUrl' => $propositionConfig->applicationUrls['backoffice'] ?? null,
            'supportUrl' => $propositionConfig->applicationUrls['support'] ?? null,
            'localCountry' => $propositionConfig->countryCode,
            'defaultCarrier' => $this->getDefaultCarrier()->name,
            'defaultCarrierId' => $this->getDefaultCarrier()->id,
            'defaultSettings' => [], // @todo no longer supported, remove in v3.0.0
            'carriers' => $this->getCarriers()->toArray(),
        ];
    }
}
