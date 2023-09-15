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

class PostOrdersRequest extends Request
{
    public function __construct(private readonly OrderCollection $collection)
    {
        parent::__construct();
    }

    /**
     * @return null|string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'orders' => array_map(fn(Order $order) => $this->encodeOrder($order), $this->collection->all()),
            ],
        ]);
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return '/fulfilment/orders';
    }

    /**
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
                    $carry[] = $orderLine->except('vat', Arrayable::CASE_SNAKE);

                    return $carry;
                },
                []
            ),
            'shipment'                      => $this->getShipment($order),
        ];
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Base\Model\ContactDetails $address
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipment(Order $order): ?array
    {
        $shipment = $order->shipment;

        return [
            'carrier'             => $shipment->carrier,
            'customs_declaration' => $shipment->customsDeclaration
                ? $shipment->customsDeclaration->toSnakeCaseArray()
                : null,
            'drop_off_point'      => $shipment->dropOffPoint
                ? $shipment->dropOffPoint->toSnakeCaseArray()
                : null,
            'options'             => $this->getShipmentOptions($shipment),
            'physical_properties' => $shipment->physicalProperties
                ? $shipment->physicalProperties->toSnakeCaseArray()
                : null,
            'pickup'              => $shipment->pickup
                ? $shipment->pickup->toSnakeCaseArray()
                : null,
            'recipient'           => $this->getAddress($shipment->recipient),
        ];
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipmentOptions(Shipment $shipment): array
    {
        $options = $shipment->options->toArray(Arrayable::CASE_SNAKE | Arrayable::SKIP_NULL);

        $options['insurance'] = [
            'amount'   => $shipment->options->insurance,
            'currency' => 'EUR',
        ];

        return array_map(static fn($item) => is_bool($item) ? (int) $item : $item, $options);
    }
}
