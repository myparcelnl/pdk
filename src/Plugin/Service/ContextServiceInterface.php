<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext;
use MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

interface ContextServiceInterface
{
    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ContextBag
     */
    public function createContexts(array $contexts, array $data = []): ContextBag;

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext
     */
    public function createGlobalContext(): GlobalContext;

    /**
     * @param  null|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext
     */
    public function createDeliveryOptionsContext(pdkOrder $order): DeliveryOptionsContext;
}
