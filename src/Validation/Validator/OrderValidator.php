<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as JsonSchemaValidator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Platform;
use RuntimeException;

class OrderValidator extends OrderPropertiesValidator implements OrderValidatorInterface
{
    /**
     * @var null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    protected $order;

    public function getSchema(): array
    {
        $this->ensureOrder();

        $deliveryOptions = $this->order->deliveryOptions;

        return $this->repository->getOrderValidationSchema(
            $deliveryOptions->carrier->name ?? Platform::get('defaultCarrier'),
            $this->order->shippingAddress->cc ?? null,
            $deliveryOptions->packageType,
            $deliveryOptions->deliveryType
        );
    }

    /**
     * @return $this
     */
    public function setOrder(PdkOrder $order): OrderValidatorInterface
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function validate(): bool
    {
        $this->ensureOrder();

        $array = $this->createOrderArray();

        $validator = new JsonSchemaValidator();
        $schema    = $this->getSchema();

        $validator->validate($array, $schema, Constraint::CHECK_MODE_TYPE_CAST);

        $this->errors      = $validator->getErrors();
        $this->description = $schema['description'] ?? null;

        return $validator->isValid();
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function createOrderArray(): array
    {
        return $this->order->toArray();
    }

    protected function ensureOrder(): void
    {
        if (! $this->order) {
            throw new RuntimeException('Order is not set.');
        }
    }
}
