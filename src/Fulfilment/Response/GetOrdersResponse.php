<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Model\OrderLine;
use MyParcelNL\Pdk\Shipment\Concern\HasEncodesShipment;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class GetOrdersResponse extends AbstractApiResponseWithBody
{
    use HasEncodesShipment;

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $orders;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    public function getOrders(): OrderCollection
    {
        return $this->orders;
    }

    /**
     * @param  string $body
     *
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(string $body): void
    {
        $parsedBody = json_decode($body, true);
        $orders     = $parsedBody['data']['orders'] ?? [];

        $orderData = [];

        foreach ($orders as $order) {
            $orderData[] = $this->createOrderFromApiData($order);
        }

        $this->orders = (new OrderCollection($orderData));
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Model\Order
     * @throws \Exception
     */
    private function createOrderFromApiData(array $data): Order
    {
        return new Order([
            'accountId'                   => null,
            'createdAt'                   => $data['created_at'],
            'externalIdentifier'          => $data['external_identifier'],
            'fulfilmentPartnerIdentifier' => $data['fulfilment_partner_identifier'],
            'invoiceAddress'              => new ContactDetails($data['invoice_address'] ?? []),
            'language'                    => $data['language'],
            'orderDate'                   => $data['order_date'],
            'orderLines'                  => $this->createOrderLines($data['order_lines']),
            'price'                       => $data['price'],
            'shipment'                    => $this->createShipment($data['shipment']),
            'shopId'                      => $data['shop_id'],
            'status'                      => $data['status'],
            'type'                        => $data['type'],
            'updatedAt'                   => $data['updated_at'],
            'uuid'                        => $data['uuid'],
            'vat'                         => $data['vat'],
        ]);
    }

    /**
     * @param $orderLinesArray
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function createOrderLines($orderLinesArray): Collection
    {
        $orderLines = array_map(static function ($item) {
            return new OrderLine($item);
        }, $orderLinesArray);

        return new OrderLineCollection($orderLines);
    }

    /**
     * @param  array $shipment
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    private function createShipment(array $shipment): Shipment
    {
        return new Shipment([
            'deliveryOptions' => new DeliveryOptions([
                'carrier'         => $shipment['carrier'],
                'date'            => $shipment['options']['delivery_date'],
                'deliveryType'    => $this->getDeliveryTypeName($shipment['options']['delivery_type']),
                'packageType'     => $this->getPackageTypeName($shipment['options']['package_type']),
                'pickupLocation'  => $shipment['pickup'],
                'shipmentOptions' => new ShipmentOptions([
                    'ageCheck'         => $shipment['options']['age_check'],
                    'insurance'        => $shipment['options']['insurance'],
                    'labelDescription' => $shipment['options']['label_description'],
                    'largeFormat'      => $shipment['options']['large_format'],
                    'onlyRecipient'    => $shipment['options']['only_recipient'],
                    'return'           => $shipment['options']['return'],
                    'sameDayDelivery'  => $shipment['options']['same_day_delivery'],
                    'signature'        => $shipment['options']['signature'],
                ]),
            ])
        ]);
    }

    /**
     * @param  null|array $item
     *
     * @return null|array
     */
    private function filter(?array $item): ?array
    {
        return array_filter($item ?? []) ?: null;
    }

    /**
     * @param  array $options
     *
     * @return array
     */
    private function getShipmentOptions(array $options): array
    {
        $keys            = array_keys((new ShipmentOptions())->getAttributes('snake'));
        $shipmentOptions = Arr::only($options, $keys);

        $shipmentOptions['insurance'] = $options['insurance']['amount'] ?? null;

        return $shipmentOptions;
    }
}
