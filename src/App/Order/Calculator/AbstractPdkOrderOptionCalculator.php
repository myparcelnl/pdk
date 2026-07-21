<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

abstract class AbstractPdkOrderOptionCalculator implements PdkOrderOptionCalculatorInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    protected $order;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Build the capabilities recipient for this order's shipping address.
     *
     * The recipient is always known at order-processing time, so the business flag is always sent
     * explicitly (true or false) — capabilities are then resolved for the correct B2B/B2C context.
     *
     * @return array{country_code: null|string, is_business: bool}
     */
    protected function capabilitiesRecipient(): array
    {
        $address = $this->order->shippingAddress;

        return [
            'country_code' => $address->cc,
            'is_business'  => $address->isBusiness,
        ];
    }
}
