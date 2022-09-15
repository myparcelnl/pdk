<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface;
use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext;
use MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Sdk\src\Support\Arr;

class ContextService implements ContextServiceInterface
{
    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ContextBag
     */
    public function createContexts(array $contexts, array $data = []): ContextBag
    {
        $context = array_reduce($contexts, function (array $acc, string $id) use ($data) {
            $acc[$id] = $this->resolveContext($id, $data);

            return $acc;
        }, []);

        return new ContextBag($context);
    }

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext
     */
    public function createGlobalContext(): GlobalContext
    {
        /** @var \MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface $endpointActions */
        $endpointActions = Pdk::get(EndpointActionsInterface::class);

        return new GlobalContext([
            'baseUrl'        => $endpointActions->getBaseUrl(),
            'endpoints'      => $endpointActions->toArray(),
            'translations'   => LanguageService::getTranslations(),
            // @todo Expose plugin settings to frontend here
            'pluginSettings' => [],
        ]);
    }

    /**
     * @param null|array|PdkOrder $orderData
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext
     */
    public function createDeliveryOptionsContext($orderData): DeliveryOptionsContext
    {
        $pdkOrder = is_a($orderData, PdkOrder::class) ? $orderData : new PdkOrder($orderData ?? []);

        return new DeliveryOptionsContext(['order' => $pdkOrder]);
    }

    /**
     * @param  null|array|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection
    {
        if (is_a($orderData, PdkOrder::class) || (is_array($orderData) && Arr::isAssoc($orderData))) {
            $orderData = [$orderData];
        }

        $orders = is_a($orderData, PdkOrderCollection::class)
            ? $orderData
            : new PdkOrderCollection($orderData);

        return new OrderDataContextCollection($orders ? $orders->all() : null);
    }

    /**
     * @param  string $contextId
     * @param  array  $data
     *
     * @return null|\MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection|\MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext|\MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext
     */
    protected function resolveContext(string $contextId, array $data = [])
    {
        switch ($contextId) {
            case Context::ID_GLOBAL:
                return $this->createGlobalContext();

            case Context::ID_ORDER_DATA:
                return $this->createOrderDataContext($data['order'] ?? null);

            case Context::ID_DELIVERY_OPTIONS_CONFIG:
                return $this->createDeliveryOptionsContext($data['order'] ?? null);
        }

        DefaultLogger::alert('Invalid context key passed.', compact('contextId', 'data'));
        return null;
    }
}
