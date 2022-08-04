<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use JsonSchema\Validator as JsonSchemaValidator;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

class OrderValidator
{
    /**
     * @var array
     */
    private $additionalSchema;

    /**
     * @var array
     */
    private $baseSchema;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    private $order;

    /**
     * @var array
     */
    private $orderArray;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __construct(PdkOrder $order)
    {
        $order->deliveryOptions->shipmentOptions->lockShipmentOptions();
        $this->order      = $order;
        $this->orderArray = $order->toArray();

        $platform = Platform::get('name');

        $this->baseSchema       = Config::get('schema/order');
        $this->additionalSchema = Config::get("validation/$platform/order");

        $this->constructValidationSchema();
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
    public function getValidationSchema(): array
    {
        return $this->baseSchema;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $validator = new JsonSchemaValidator();
        $validator->validate($this->orderArray, $this->baseSchema);

        $this->errors = $validator->getErrors();

        return $validator->isValid();
    }

    /**
     * @return void
     */
    private function constructValidationSchema(): void
    {
        /** @var \MyParcelNL\Pdk\Base\Service\CountryService $countryService */
        $countryService = Pdk::get(CountryService::class);

        $deliveryOptions = $this->order->deliveryOptions;
        $deliveryOptions->shipmentOptions->lockShipmentOptions();

        $carrier = $deliveryOptions->getCarrier() ?? Platform::get('defaultCarrier');

        $this->mergeIntoSchema('carrier', 'name', $carrier)
            ->mergeIntoSchema('shippingZone', 'cc', $countryService->getShippingZone($this->order->recipient->cc))
            ->mergeIntoSchema('packageType', 'name', $deliveryOptions->getPackageType())
            ->mergeIntoSchema('deliveryType', 'name', $deliveryOptions->getDeliveryType());
    }

    /**
     * @param  string $attribute
     * @param  string $column
     * @param  mixed  $value
     *
     * @return $this
     */
    private function mergeIntoSchema(string $attribute, string $column, $value): self
    {
        $extras        = $this->additionalSchema[$attribute] ?? [];
        $index         = array_search($value, array_column($extras, $column), true) ?: 0;
        $matchedExtras = $extras[$index];
        $platform      = Pdk::get('platform');

        if (isset($matchedExtras['schema'])) {
            $schema      = $matchedExtras['schema'];
            $schemaArray = is_string($schema)
                ? Config::get("schema/$platform/$schema")
                : $schema;

            $this->baseSchema = $this->mergeSchemasRecursively($this->baseSchema, $schemaArray);
        }

        $this->additionalSchema = $matchedExtras;

        return $this;
    }

    /**
     * @param  array $current
     * @param  array $previous
     *
     * @return array
     */
    private function mergeSchemasRecursively(array $current, array $previous): array
    {
        foreach ($previous as $key => $value) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            if (is_array($value) && ! in_array($key, ['enum', 'type'])) {
                $value = $this->mergeSchemasRecursively($current[$key], $value);
            }

            $current[$key] = $value;
        }

        return $current;
    }
}
