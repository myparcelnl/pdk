<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class OrdersResponse extends AbstractApiResponseWithBody
{
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
     */
    private function createOrderFromApiData(array $data): Order
    {
        $options            = $data['options'] ?? [];
        $physicalProperties = $data['physical_properties'] ?? [];

        return new Order([
            'uuid'                     => $data['uuid'],
            'shopId'                   => $data['shop_id'],
            'status'                   => $data['status'],
            'externalIdentifier'       => $data['external_identifier'],
            'recipient'                => $this->filter($data['recipient']),
            'orderLines'               => $this->createOrderLines($data['order_lines']),
            'deliveryOptions'          => [
                'deliveryType'    => $options['delivery_type'],
                'packageType'     => $options['package_type'],
                'shipmentOptions' => $this->getShipmentOptions($options),
                'pickupLocation'  => $this->filter($data['pickup']),
            ],
            'dropOffPoint'             => $this->filter($data['drop_off_point']),
            'customsDeclaration'       => $this->filter($data['customs_declaration']),
            'physicalProperties'       => $physicalProperties
                ? Arr::only($physicalProperties, ['height', 'length', 'weight', 'width'])
                : null,
            'collectionContact'        => $data['collection_contact'],
            'delayed'                  => $data['delayed'],
            'delivered'                => $data['delivered'],
            'linkConsumerPortal'       => $data['link_consumer_portal'],
            'multiColloMainShipmentId' => $data['multi_collo_main_shipment_id'],
            'partnerTrackTraces'       => $data['partner_tracktraces'],
            'referenceIdentifier'      => $data['reference_identifier'],
            'updated'                  => $data['updated'],
            'created'                  => $data['created'],
            'createdBy'                => $data['created_by'],
            'modified'                 => $data['modified'],
            'modifiedBy'               => $data['modified_by'],
            'multiCollo'               => $data['multi_collo_main_shipment_id'] && $data['secondary_shipments'],
        ]);
    }

    private function createOrderLines($order_lines): Collection
    {
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
