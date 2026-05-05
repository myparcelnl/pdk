<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use DateTimeZone;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Concern\HasCarrierAttribute;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * @property null|int                                               $id
 * @property null|int                                               $shopId
 * @property null|string                                            $orderId
 * @property null|string                                            $referenceIdentifier
 * @property null|string                                            $externalIdentifier
 * @property null|string                                            $barcode
 * @property \MyParcelNL\Pdk\Carrier\Model\Carrier                  $carrier
 * @property null|string                                            $contractId
 * @property null|string                                            $collectionContact
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property bool                                                   $delayed
 * @property bool                                                   $delivered
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions    $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $dropOffPoint
 * @property bool                                                   $hidden
 * @property bool                                                   $isReturn
 * @property null|string                                            $linkConsumerPortal
 * @property bool                                                   $multiCollo
 * @property null|string                                            $multiColloMainShipmentId
 * @property array                                                  $partnerTrackTraces
 * @property null|\MyParcelNL\Pdk\Shipment\Model\PhysicalProperties $physicalProperties
 * @property \MyParcelNL\Pdk\Base\Model\Currency                    $price
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $sender
 * @property null|int                                               $shipmentType
 * @property null|int                                               $status
 * @property null|\DateTime                                         $deleted
 * @property null|\DateTime                                         $updated
 * @property null|\DateTime                                         $created
 * @property null|int                                               $createdBy
 * @property null|\DateTime                                         $modified
 * @property null|int                                               $modifiedBy
 */
class Shipment extends Model
{
    use HasCarrierAttribute {
        setCarrierAttribute as protected setCarrierAttributeFromTrait;
    }

    public const SHIPMENT_TYPE_STANDARD       = 1;
    public const SHIPMENT_TYPE_RETURN         = 2;
    public const SHIPMENT_TYPE_MULTI_COLLO    = 3;
    public const SHIPMENT_TYPE_LABEL_PACKAGE  = 4;
    public const SHIPMENT_TYPE_RETURN_ERS     = 5;
    public const SHIPMENT_TYPE_RETURN_SPECIAL = 6;
    public const SHIPMENT_TYPE_EXPRESS        = 7;
    public const RETURN_SHIPMENT_TYPES        = [
        self::SHIPMENT_TYPE_RETURN,
        self::SHIPMENT_TYPE_RETURN_ERS,
        self::SHIPMENT_TYPE_RETURN_SPECIAL,
    ];

    protected $attributes = [
        /**
         * ID of the order this shipment belongs to, if any.
         */
        'orderId'                  => null,

        /**
         * MultiCollo shipments are shipments that are split into multiple shipments.
         */
        'multiCollo'               => false,

        /**
         * The date and time when the shipment was last updated. Supposed to be saved in the plugin.
         */
        'updated'                  => null,

        /**
         * PLUGIN ONLY: Whether the shipment is deleted.
         */
        'deleted'                  => null,

        /**
         * Shipment ID. Filled by the API after exporting shipment.
         */
        'id'                       => null,
        'shopId'                   => null,
        'referenceIdentifier'      => null,
        'externalIdentifier'       => null,
        'barcode'                  => null,
        'carrier'                  => null,
        'contractId'               => null,
        'collectionContact'        => null,
        'customsDeclaration'       => null,
        'delayed'                  => false,
        'delivered'                => false,
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => null,
        'hidden'                   => false,
        'isReturn'                 => false,

        /**
         * The link to the track and trace page of the consumer portal.
         */
        'linkConsumerPortal'       => null,

        /**
         * Main shipment if this shipment belongs to a multi-collo shipment.
         */
        'multiColloMainShipmentId' => null,

        /**
         * Partner track traces are track traces of other carriers that are linked to this shipment.
         */
        'partnerTrackTraces'       => [],

        /**
         * Physical properties of the shipment.
         */
        'physicalProperties'       => null,

        /**
         * The billed price of the shipment.
         */
        'price'                    => Currency::class,

        /**
         * The recipient of the shipment.
         */
        'recipient'                => null,

        /**
         * The sender of the shipment.
         */
        'sender'                   => null,

        /**
         * Type of shipment.
         */
        'shipmentType'             => null,

        /**
         * Status of the shipment.
         *
         * @see https://developer.myparcel.nl/api-reference/04.data-types.html#shipment-status
         */
        'status'                   => null,

        /**
         * The date and time when the shipment was created in the API.
         */
        'created'                  => null,

        /**
         * The id of the user that created the shipment in the API.
         */
        'createdBy'                => null,

        /**
         * The date and time when the shipment was last updated in the API.
         */
        'modified'                 => null,

        /**
         * The id of the user that last updated the shipment in the API.
         */
        'modifiedBy'               => null,
    ];

    protected $casts      = [
        'id'                       => 'int',
        'shopId'                   => 'int',
        'orderId'                  => 'string',
        'referenceIdentifier'      => 'string',
        'externalIdentifier'       => 'string',
        'barcode'                  => 'string',
        'collectionContact'        => 'string',
        'contractId'               => 'string',
        'customsDeclaration'       => CustomsDeclaration::class,
        'delayed'                  => 'bool',
        'delivered'                => 'bool',
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => RetailLocation::class,
        'hidden'                   => 'bool',
        'isReturn'                 => 'bool',
        'linkConsumerPortal'       => 'string',
        'multiCollo'               => 'bool',
        'multiColloMainShipmentId' => 'string',
        'partnerTrackTraces'       => 'array',
        'physicalProperties'       => PhysicalProperties::class,
        'price'                    => Currency::class,
        'recipient'                => ContactDetails::class,
        'sender'                   => ContactDetails::class,
        'shipmentType'             => 'int',
        'status'                   => 'int',
        'deleted'                  => DateTime::class,
        'updated'                  => DateTime::class,
        'created'                  => DateTime::class,
        'createdBy'                => 'int',
        'modified'                 => DateTime::class,
        'modifiedBy'               => 'int',
    ];

    /**
     * Carrier is passed to the delivery options.
     *
     * @param  null|array $data
     *
     * @throws \Exception
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->updateCarrier();
    }

    /**
     * Builds a Shipment from supported webhook fields only. The mapping is explicit because webhook payloads can use
     * multiple aliases for the same shipment attribute.
     *
     * @param  array     $payload
     * @param  string    $orderId
     * @param  null|self $existingShipment
     * @param  bool      $preserveExistingStatus
     *
     * @return self
     * @throws \Exception
     */
    public static function fromWebhookPayload(
        array $payload,
        string $orderId,
        ?self $existingShipment = null,
        bool $preserveExistingStatus = false
    ): self {
        $data = self::getWebhookPayloadData($payload, $orderId);

        if ($existingShipment) {
            $existingData = $existingShipment->toStorableArray();

            if ($preserveExistingStatus && isset($data['status'], $existingData['status'])) {
                unset($data['status']);
            }

            $data = array_replace($existingData, $data);
        }

        return new self($data);
    }

    /**
     * @param  array $payload
     *
     * @return null|int
     */
    public static function getIdFromWebhookPayload(array $payload): ?int
    {
        $payload = self::getWebhookShipmentContent($payload);

        foreach (['shipment_id', 'shipmentId', 'id'] as $key) {
            $value = self::getTrimmedValue($payload, $key);

            if ('' !== $value && is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Returns the model as an array that can be saved in a database.
     *
     * @return array
     * @throws \Exception
     */
    public function toStorableArray(): array
    {
        if (null === $this->updated) {
            $timeZone      = new DateTimeZone(Pdk::get('defaultTimeZone'));
            $this->updated = new DateTime('now', $timeZone);
        }

        $array = $this->except([
            'customsDeclaration',
            'physicalProperties',
            'recipient',
            'sender',
        ], Arrayable::STORABLE_NULL);

        // Carrier should be the (raw) name only, not the full resolved carrier data.
        $array['carrier'] = $this->attributes['carrier'];

        return $array;
    }

    /**
     * @param  array  $payload
     * @param  string $orderId
     *
     * @return array
     */
    private static function getWebhookPayloadData(array $payload, string $orderId): array
    {
        $payload = self::getWebhookShipmentContent($payload);

        return self::filterEmptyValues([
            'id'                       => self::getIdFromWebhookPayload($payload),
            'orderId'                  => $orderId,
            'referenceIdentifier'      => $orderId,
            'externalIdentifier'       => self::getFirstTrimmedValue($payload, [
                'external_identifier',
                'external_shipment_identifier',
                'externalIdentifier',
            ]),
            'barcode'                  => self::getFirstTrimmedValue($payload, ['barcode']),
            'linkConsumerPortal'       => self::getFirstTrimmedValue($payload, [
                'link_consumer_portal',
                'linkConsumerPortal',
                'track_trace_url',
                'trackTraceUrl',
            ]),
            'multiColloMainShipmentId' => self::getFirstTrimmedValue($payload, [
                'multi_collo_main_shipment_id',
                'multiColloMainShipmentId',
            ]),
            'status'                   => self::getNullableInt($payload, 'status'),
            'shipmentType'             => self::getNullableInt($payload, 'shipment_type'),
            'isReturn'                 => self::getNullableBool($payload, 'is_return'),
            'multiCollo'               => self::getNullableBool($payload, 'multi_collo'),
        ]);
    }

    /**
     * @param  array $payload
     *
     * @return array
     */
    public static function getWebhookShipmentContent(array $payload): array
    {
        return isset($payload['shipment']) && is_array($payload['shipment'])
            ? array_replace($payload, $payload['shipment'])
            : $payload;
    }

    /**
     * @param  array  $payload
     * @param  string $key
     *
     * @return string
     */
    private static function getTrimmedValue(array $payload, string $key): string
    {
        return isset($payload[$key]) ? trim((string) $payload[$key]) : '';
    }

    /**
     * @param  array    $payload
     * @param  string[] $keys
     *
     * @return null|string
     */
    private static function getFirstTrimmedValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = self::getTrimmedValue($payload, $key);

            if ('' !== $value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array  $payload
     * @param  string $key
     *
     * @return null|int
     */
    private static function getNullableInt(array $payload, string $key): ?int
    {
        $value = self::getTrimmedValue($payload, $key);

        return '' === $value || ! is_numeric($value) ? null : (int) $value;
    }

    /**
     * @param  array  $payload
     * @param  string $key
     *
     * @return null|bool
     */
    private static function getNullableBool(array $payload, string $key): ?bool
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        return filter_var($payload[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param  array $data
     *
     * @return array
     */
    private static function filterEmptyValues(array $data): array
    {
        return array_filter($data, static function ($value): bool {
            return null !== $value && '' !== $value;
        });
    }

    /**
     * @param  string|\MyParcelNL\Pdk\Carrier\Model\Carrier|array|null $carrier
     *
     * @return $this
     * @throws \Exception
     */
    protected function setCarrierAttribute($carrier): self
    {
        $this->setCarrierAttributeFromTrait($carrier);
        $this->updateCarrier();

        return $this;
    }

    /**
     * @param  array|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return $this
     * @throws \Exception
     */
    protected function setDeliveryOptionsAttribute($deliveryOptions): self
    {
        $this->attributes['deliveryOptions'] = $deliveryOptions;
        $this->updateCarrier();

        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function updateCarrier(): void
    {
        // In case the model hasn't fully initialized yet (e.g. deliveryOptions is still a class string).
        if (is_string($this->attributes['deliveryOptions'])) {
            return;
        }

        // $this->attributes['carrier'] is now always null|string (carrier name), never a class string.
        if ($this->attributes['carrier']) {
            $this->attributes['deliveryOptions']['carrier'] = $this->attributes['carrier'];
        } else {
            // Pull the carrier name from the deliveryOptions attributes directly to avoid going through
            // the getter (which may resolve to the default carrier).
            $doAttributes = is_array($this->attributes['deliveryOptions'])
                ? $this->attributes['deliveryOptions']
                : $this->deliveryOptions->getAttributes();
            $carrierName = $doAttributes['carrier'] ?? null;
            if ($carrierName && is_string($carrierName)) {
                $this->attributes['carrier'] = $carrierName;
            }
        }
    }
}
