<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Model\SdkBackedModel;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

/**
 * Instantiate a Carrier model based on existing known data when passed an ID/Name, or creates a new Carrier model based on the data passed to the constructor.
 * If nothing is passed, the configured default carrier is returned.
 *
 * This Carrier model is modelled on top of the carrier as returned by the shipments/capabilities endpoint with additional metadata.
 * This gives us the relevant information about the carrier that we need to use, where the other API endpoints only concern themselves with being passed the ID/Name of the carrier.
 *
 *
 * Properties from the backing RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2 SDK model
 * @property string $carrier          Carrier name in CONSTANT_CASE from contract definitions
 * @property string[]|null  $packageTypes     Available package types as an array of CONSTANT_CASE strings from contract definitions
 * @property \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2|null  $options          Available shipment options including default/required states and additional metadata for options (e.g. insurance suboptions and their constraints)
 * @property string[]|null  $deliveryTypes    Available delivery types as an array of CONSTANT_CASE strings from contract definitions
 * @property string[]|null  $transactionTypes Available transaction types as an array of CONSTANT_CASE strings from contract definitions
 * @property \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCollo|null  $collo            Collo constraints
 */
class Carrier extends SdkBackedModel
{
    /**
     * Transient contract ID from capabilities response. Not persisted.
     *
     * @var int|null
     */
    public $contractId;

    /*
     * Inherit all getters and setters from this model.
     */
    protected $sdkModelClass = RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2::class;

    /**
     * Whether the SDK can map a V2 carrier name to a v1 export ID.
     *
     * Carriers without an ID are filtered out at the boundary
     * ({@see \MyParcelNL\Pdk\Account\Service\AccountSettingsService::getCarriers},
     * {@see \MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction}'s response)
     * so a server-side proposition update introducing a new carrier cannot expose
     * it to admin or checkout, which would otherwise lead to encode-side throws
     * during export. A new carrier becomes supported with an SDK update.
     *
     * @param  string $carrierName
     *
     * @return bool
     */
    public static function isSupported(string $carrierName): bool
    {
        return null !== ApiMapperService::forCarrier()->idFromV2Name($carrierName);
    }

    /**
     * Translate a numeric carrier id (as exposed by external APIs in legacy CoreAPI shape)
     * to its V2 carrier name (e.g. 1 → "POSTNL", 15 → "BRT"). Returns null when the SDK
     * has no V2 name for the id.
     *
     * Pure SDK lookup with no shop/repository dependency, so it is safe to call
     * before any carrier collection has been resolved or persisted.
     *
     * @param  int $id Numeric carrier id from a legacy CoreAPI payload.
     *
     * @return null|string V2 carrier name, or null when the SDK has no V2 name for the id.
     */
    public static function v2NameFromLegacyId(int $id): ?string
    {
        return ApiMapperService::forCarrier()->v2NameFromId($id);
    }

    /**
     * Any attributes here extend/overwrite the data from RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2.
     * @see RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2
     */
    protected $attributes = [];

    /**
     * Any attributes here extend/overwrite the getters from RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2.
     * @see RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2
     */
    protected $casts = [];

    /**
     * Create a new Carrier model instance with the provided data.
     *
     * To fetch existing carriers from account data, use CarrierRepository instead.
     *
     * @param  null|array $data
     * @see \MyParcelNL\Pdk\Carrier\Repository\CarrierRepository
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data ?? []);
    }

    /**
     * Cached allowlist of registered capabilities keys, shared across all Carrier instances.
     *
     * @var null|array<string, true>
     */
    private static $registeredCapabilitiesKeys;

    /**
     * Allowlist of camelCase option keys that have a registered OrderOptionDefinition in this PDK.
     *
     * Capabilities responses may carry options the PDK has no calculator/UI label for; those
     * are stripped at the SDK boundary so they never reach the admin or checkout. Keys are
     * camelCase to match the SDK options model's attributeMap. Cached because definitions
     * don't change at runtime.
     *
     * @return array<string, true>
     */
    public static function getRegisteredCapabilitiesKeys(): array
    {
        if (self::$registeredCapabilitiesKeys === null) {
            /** @var OrderOptionDefinitionInterface[] $definitions */
            $definitions = Pdk::get('orderOptionDefinitions');

            self::$registeredCapabilitiesKeys = [];

            foreach ($definitions as $definition) {
                $key = $definition->getCapabilitiesOptionsKey();

                if ($key !== null) {
                    self::$registeredCapabilitiesKeys[$key] = true;
                }
            }
        }

        return self::$registeredCapabilitiesKeys;
    }

    /**
     * Utility helper to directly get the option definition for a shipment option by its capabilities key.
     * Avoids having to chain through multiple levels of getters and null checks to get to the same data, as this is a common action when working with carriers and their options.
     *
     * @param  string $capabilitiesKey camelCase key, e.g. 'requiresSignature'
     *
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsBaseOptionV2
     */
    public function getOptionMetadata(string $capabilitiesKey)
    {
        if (! $this->options) {
            return null;
        }

        $getter = 'get' . ucfirst($capabilitiesKey);

        if (! method_exists($this->options, $getter)) {
            return null;
        }

        $option = $this->options->$getter();

        if (! $option || ! method_exists($option, 'getIsRequired')) {
            return null;
        }

        // Return type only type-hinted in comments, as the actual return type is a union of SDK types which is not supported in PHP 7.4
        return $option;
    }
}
