<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Model\OrderLine;
use MyParcelNL\Pdk\Fulfilment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Concern\EncodesCustomsDeclaration;

class PostOrdersRequest extends Request
{
    use EncodesCustomsDeclaration;

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     */
    public function __construct(OrderCollection $collection)
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
                'orders' => array_map(function (Order $order) {
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
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOrder(Order $order): array
    {
        return [
            'external_identifier'           => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'invoice_address'               => $this->getAddress($order->invoiceAddress),
            'order_date'                    => $order->orderDate
                ? $order->orderDate->format(Pdk::get('defaultDateFormat'))
                : null,
            'order_lines'                   => $order->lines->reduce(
                function (array $carry, OrderLine $orderLine) {
                    $carry[] = $orderLine->except('vat', Arrayable::ENCODED);

                    return $carry;
                },
                []
            ),
            'shipment'                      => $this->getShipment($order),
        ];
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Base\Model\ContactDetails $address
     *
     * @return null|array
     */
    private function getAddress(?ContactDetails $address): ?array
    {
        if (! $address) {
            return null;
        }

        return Utils::filterNull([
            'street'      => implode(' ', [$address->address1, $address->address2]),
            'city'        => $address->city,
            'area'        => $address->area,
            'company'     => $address->company,
            'cc'          => $address->cc,
            'email'       => $address->email,
            'person'      => $address->person,
            'phone'       => $address->phone,
            'postal_code' => $address->postalCode,
            'region'      => $address->region,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipment(Order $order): array
    {
        $shipment = $order->shipment;

        return [
            'carrier'             => $shipment->carrier,
            'customs_declaration' => $this->encodeCustomsDeclaration($shipment),
            'drop_off_point'      => $shipment->dropOffPoint
                ? $shipment->dropOffPoint->toArray(Arrayable::ENCODED)
                : null,
            'options'             => $this->getShipmentOptions($shipment),
            'physical_properties' => $shipment->physicalProperties->toArray(Arrayable::ENCODED),
            'pickup'              => $shipment->pickup
                ? $shipment->pickup->toArray(Arrayable::ENCODED)
                : null,
            'recipient'           => $this->getAddress($shipment->recipient),
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipmentOptions(Shipment $shipment): array
    {
        $options = $shipment->options->toArray(Arrayable::ENCODED);

        $options['insurance'] = [
            'amount'   => $shipment->options->insurance,
            'currency' => 'EUR',
        ];

        return array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $options);
    }
}
