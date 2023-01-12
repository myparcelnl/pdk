<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use BadMethodCallException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as JsonSchemaValidator;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Sdk\src\Support\Str;
use RuntimeException;

/**
 * @method canHaveMultiCollo(): bool
 * @method canHaveSignature(): bool
 * @method canHaveInsurance(int $value = 100): bool
 * @method canHaveOnlyRecipient(): bool
 * @method canHaveAgeCheck(): bool
 * @method canHaveLargeFormat(): bool
 * @method canHaveWeight(int $value = 1): bool
 * @method canHaveDate(): bool
 */
class OrderValidator implements ValidatorInterface
{
    /**
     * @var array{string: string}
     */
    private const METHOD_BOOLEAN_KEYS_MAP = [
        'ageCheck'      => self::SHIPMENT_OPTIONS_KEY . '.ageCheck',
        'largeFormat'   => self::SHIPMENT_OPTIONS_KEY . '.largeFormat',
        'multiCollo'    => 'properties.multiCollo',
        'onlyRecipient' => self::SHIPMENT_OPTIONS_KEY . '.onlyRecipient',
        'signature'     => self::SHIPMENT_OPTIONS_KEY . '.signature',
    ];
    /**
     * @var array{string: string}
     */
    private const METHOD_VALUE_KEYS_MAP = [
        'date'      => 'properties.deliveryOptions.properties.date',
        'insurance' => self::SHIPMENT_OPTIONS_KEY . '.insurance',
        'weight'    => 'properties.physicalProperties.properties.weight',
    ];
    private const SHIPMENT_OPTIONS_KEY  = 'properties.deliveryOptions.properties.shipmentOptions.properties';

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    protected $repository;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    private $order;

    /**
     * @var array
     */
    private $orderArray;

    /**
     * @param  \MyParcelNL\Pdk\Validation\Repository\SchemaRepository $repository
     */
    public function __construct(SchemaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  string $methodName
     * @param  mixed  $args
     *
     * @return bool
     */
    public function __call(string $methodName, $args)
    {
        $property = Str::camel(Str::after($methodName, 'canHave'));

        if (array_key_exists($property, self::METHOD_VALUE_KEYS_MAP)) {
            return $this->repository->validateOption(
                $this->getSchema(),
                self::METHOD_VALUE_KEYS_MAP[$property],
                $args[0]
            );
        }

        if (array_key_exists($property, self::METHOD_BOOLEAN_KEYS_MAP)) {
            return $this->repository->validateOption(
                $this->getSchema(),
                self::METHOD_BOOLEAN_KEYS_MAP[$property],
                true
            );
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist.', $methodName));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getSchema(): array
    {
        if (! $this->order) {
            throw new RuntimeException('Order is not set.');
        }

        $deliveryOptions = $this->order->deliveryOptions;

        return $this->repository->getCapabilitiesSchema(
            $deliveryOptions->carrier ?? Platform::get('defaultCarrier'),
            $this->order->recipient->cc ?? null,
            $deliveryOptions->packageType,
            $deliveryOptions->deliveryType
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return self
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function setOrder(PdkOrder $order): self
    {
        $this->lockShipmentOptions($order);

        $this->order      = $order;
        $this->orderArray = $order->toArray();

        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $validator = new JsonSchemaValidator();
        $validator->validate($this->orderArray, $this->getSchema(), Constraint::CHECK_MODE_TYPE_CAST);

        $this->errors = $validator->getErrors();

        return $validator->isValid();
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return void
     */
    private function lockShipmentOptions(PdkOrder $order): void
    {
        $order->deliveryOptions->shipmentOptions->fill([
            'ageCheck'         => (bool) $order->deliveryOptions->shipmentOptions->ageCheck,
            'insurance'        => (int) $order->deliveryOptions->shipmentOptions->insurance,
            'labelDescription' => (string) $order->deliveryOptions->shipmentOptions->labelDescription,
            'largeFormat'      => (bool) $order->deliveryOptions->shipmentOptions->largeFormat,
            'onlyRecipient'    => (bool) $order->deliveryOptions->shipmentOptions->onlyRecipient,
            'return'           => (bool) $order->deliveryOptions->shipmentOptions->return,
            'sameDayDelivery'  => (bool) $order->deliveryOptions->shipmentOptions->sameDayDelivery,
            'signature'        => (bool) $order->deliveryOptions->shipmentOptions->signature,
        ]);
    }
}
