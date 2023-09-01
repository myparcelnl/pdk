<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use Behat\Gherkin\Node\TableNode;
use Exception;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use function MyParcelNL\Pdk\Tests\factory;

final class PdkOrderContext extends AbstractContext
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param  null|string $name
     * @param  array       $data
     * @param  string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    }

    /**
     * @Given an order with id :id exists
     * @Given an order with id :id exists with data:
     *
     * @param  string                             $id
     * @param  null|\Behat\Gherkin\Node\TableNode $data
     *
     * @return void
     */
    public function anOrderWithIdExists(string $id, ?TableNode $data = null): void
    {
        factory(PdkOrder::class)
            ->withExternalIdentifier($id)
            ->with($this->parseTable($data) ?? [])
            ->store();
    }

    /**
     * @Then I expect order :orderId to have :amount shipment
     * @Then I expect order :orderId to have :amount shipments
     */
    public function iExpectOrderToHaveShipments(string $orderId, int $amount): void
    {
        $order        = $this->retrieveOrder($orderId);
        $actualAmount = $order->shipments->count();

        if ($actualAmount !== $amount) {
            self::fail("Order with id $orderId has $actualAmount shipments, expected $amount");
        }
    }

    /**
     * @Given I expect order :id to have the following delivery options:
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function iExpectOrderToHaveTheFollowingDeliveryOptions(string $orderId, TableNode $table): void
    {
        $order           = $this->retrieveOrder($orderId);
        $deliveryOptions = $order->deliveryOptions->toArray();

        foreach ($this->parseTable($table) as $key => $expectedValue) {
            $actualValue = Arr::get($deliveryOptions, $key, 'null');

            if ((string) $actualValue === $expectedValue) {
                continue;
            }

            self::fail("Order with id $orderId has $key = $actualValue, expected $expectedValue");
        }
    }

    /**
     * @Given I expect order :id not to be exported to MyParcel
     */
    public function iExpectTheOrderNotToBeExportedToMyParcel(string $orderId): void
    {
        $order = $this->retrieveOrder($orderId);

        if ($order->exported) {
            self::fail("Order with id $orderId was exported");
        }
    }

    /**
     * @Given I expect order :id to be exported to MyParcel
     */
    public function iExpectTheOrderToBeExportedToMyParcel(string $orderId): void
    {
        $order = $this->retrieveOrder($orderId);

        if (! $order->exported) {
            self::fail("Order with id $orderId was not exported");
        }
    }

    /**
     * @param  null|string $orderId
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private function retrieveOrder(?string $orderId = null): PdkOrder
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            self::fail("Order with id $orderId could not be retrieved: {$e->getMessage()}");
        }

        return $order;
    }
}
