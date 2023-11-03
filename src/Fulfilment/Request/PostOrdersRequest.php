<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use DateTimeInterface;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRequestShipment;

class PostOrdersRequest extends Request
{
    use EncodesRequestShipment;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $collection
     */
    public function __construct(PdkOrderCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'orders' => array_map(function (PdkOrder $order) {
                    return $this->encodeOrder($order);
                }, $this->collection->all()),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/fulfilment/orders';
    }

    /**
     * @param  null|\DateTimeInterface $date
     *
     * @return null|string
     */
    private function encodeDate(?DateTimeInterface $date): ?string
    {
        return $date ? $date->format(Pdk::get('defaultDateFormat')) : null;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \Exception
     */
    private function encodeOrder(PdkOrder $order): array
    {
        return [
            'external_identifier' => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'invoice_address' => $order->billingAddress
                ? $this->encodeAddress($order->billingAddress)
                : null,
            'order_date' => $this->encodeDate($order->orderDate),
            'order_lines' => $this->encodeOrderLines($order),
            'shipment' => $this->encodeShipment($order->shipments->last()),
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return array
     */
    private function encodeOrderLines(PdkOrder $order): array
    {
        return $order->lines->reduce(
            function (array $carry, PdkOrderLine $orderLine) {
                $carry[] = $orderLine->except('vat', Arrayable::ENCODED);

                return $carry;
            },
            []
        );
    }
}
