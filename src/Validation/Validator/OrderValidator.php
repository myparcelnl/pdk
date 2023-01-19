<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as JsonSchemaValidator;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use RuntimeException;

class OrderValidator extends OrderPropertiesValidator implements ValidatorInterface
{
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

        return $this->repository->getOrderValidationSchema(
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
