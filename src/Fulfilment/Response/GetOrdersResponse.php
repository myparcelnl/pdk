<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use function array_map;

class GetOrdersResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected $orders;

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function getOrders(): PdkOrderCollection
    {
        return $this->orders;
    }

    /**
     * @param  array $order
     *
     * @return array
     */
    protected function decodeOrder(array $order): array
    {
        return [
            'apiIdentifier'               => Arr::get($order, 'uuid'),
            'shopId'                      => Arr::get($order, 'shop_id'),
            'accountId'                   => Arr::get($order, 'account_id'),
            'externalIdentifier'          => Arr::get($order, 'external_identifier'),
            'fulfilmentPartnerIdentifier' => Arr::get($order, 'fulfilment_partner_identifier'),
            'orderDate'                   => Arr::get($order, 'order_date'),
            'status'                      => Arr::get($order, 'status'),
            'invoiceAddress'              => Arr::get($order, 'invoice_address'),
            'price'                       => Arr::get($order, 'price'),
            'vat'                         => Arr::get($order, 'vat'),
            'priceAfterVat'               => Arr::get($order, 'price_after_vat'),
            'createdAt'                   => Arr::get($order, 'created_at'),
            'updatedAt'                   => Arr::get($order, 'updated_at'),

            'deliveryOptions' => [
                DeliveryOptions::CARRIER          => Arr::get($order, 'shipment.carrier'),
                DeliveryOptions::DATE             => Arr::get($order, 'shipment.options.delivery_date'),
                DeliveryOptions::PICKUP_LOCATION  => Arr::get($order, 'shipment.pickup'),
                DeliveryOptions::DELIVERY_TYPE    => Arr::get($order, 'shipment.options.delivery_type'),
                DeliveryOptions::PACKAGE_TYPE     => Arr::get($order, 'shipment.options.package_type'),
                DeliveryOptions::SHIPMENT_OPTIONS => Arr::get($order, 'shipment.options'),
            ],

            'lines'     => array_map(static function (array $orderLine) {
                $product = $orderLine['product'];

                return [
                    'apiIdentifier'   => $orderLine['uuid'] ?? null,
                    'quantity'        => $orderLine['quantity'] ?? null,
                    'instructions'    => $orderLine['instructions'] ?? null,
                    'price'           => $orderLine['price'] ?? null,
                    'price_after_vat' => $orderLine['price_after_vat'] ?? null,
                    'vat'             => $orderLine['vat'] ?? null,
                    'product'         => [
                        'externalIdentifier' => $product['external_identifier'] ?? null,
                        'description'        => $product['description'] ?? null,
                        'height'             => $product['height'] ?? null,
                        'length'             => $product['length'] ?? null,
                        'name'               => $product['name'] ?? null,
                        'weight'             => $product['weight'] ?? null,
                        'width'              => $product['width'] ?? null,
                    ],
                ];
            }, Arr::get($order, 'order_lines', [])),
            'shipments' => $this->getShipments($order['shipment'] ?? []),
        ];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $orders     = $parsedBody['data']['orders'] ?? [];

        $this->orders = new PdkOrderCollection(array_map([$this, 'decodeOrder'], $orders));
    }

    /**
     * @param  array $shipment
     *
     * @return array|array[]
     */
    private function getShipments(array $shipment): array
    {
        // array (
        //  'recipient' =>
        //  array (
        //    'cc' => 'NL',
        //    'city' => 'Hoofddorp',
        //    'number' => '31',
        //    'person' => 'Felicia Parcel',
        //    'postal_code' => '2132 JE',
        //    'street' => 'Antareslaan',
        //  ),
        //  'pickup' => NULL,
        //  'drop_off_point' => NULL,
        //  'options' =>
        //  array (
        //    'package_type' => 2,
        //    'delivery_type' => 2,
        //    'delivery_date' => '2022-11-24 20:16:50',
        //    'signature' => 0,
        //    'only_recipient' => 0,
        //    'age_check' => 0,
        //    'large_format' => 0,
        //    'return' => 0,
        //    'label_description' => '#1025',
        //  ),
        //  'physical_properties' =>
        //  array (
        //    'weight' => 35,
        //  ),
        //  'customs_declaration' => NULL,
        //  'carrier' => 1,
        //  'contract_id' => NULL,
        //)

        $street = trim(
            implode(' ', [
                Arr::get($shipment, 'recipient.street'),
                Arr::get($shipment, 'recipient.number'),
                Arr::get($shipment, 'recipient.number_suffix'),
            ])
        );

        return [
            [
                'apiIdentifier'      => Arr::get($shipment, 'uuid'),
                'trackingCode'       => Arr::get($shipment, 'tracking_code'),
                'status'             => Arr::get($shipment, 'status'),
                'recipient'          => [
                    'person'     => Arr::get($shipment, 'recipient.person'),
                    'company'    => Arr::get($shipment, 'recipient.company_name'),
                    'email'      => Arr::get($shipment, 'recipient.email'),
                    'phone'      => Arr::get($shipment, 'recipient.phone'),
                    'address1'   => $street,
                    'postalCode' => Arr::get($shipment, 'recipient.postal_code'),
                    'city'       => Arr::get($shipment, 'recipient.city'),
                    'cc'         => Arr::get($shipment, 'recipient.cc'),
                ],
                'deliveryOptions'    => [
                    'carrier'        => Arr::get($shipment, 'carrier'),
                    'package_type'   => Arr::get($shipment, 'options.package_type'),
                    'delivery_type'  => Arr::get($shipment, 'options.delivery_type'),
                    'delivery_date'  => Arr::get($shipment, 'options.delivery_date'),
                    'signature'      => Arr::get($shipment, 'options.signature'),
                    'only_recipient' => Arr::get($shipment, 'options.only_recipient'),
                    'age_check'      => Arr::get($shipment, 'options.age_check'),
                    'large_format'   => Arr::get($shipment, 'options.large_format'),
                    'return'         => Arr::get($shipment, 'options.return'),
                    'pickup'         => Arr::get($shipment, 'pickup'),
                ],
                'dropOffPoint'       => Arr::get($shipment, 'drop_off_point'),
                'physicalProperties' => [
                    'weight' => Arr::get($shipment, 'physical_properties.weight'),
                ],
                'createdAt'          => Arr::get($shipment, 'created_at'),
                'updatedAt'          => Arr::get($shipment, 'updated_at'),
            ],
        ];
    }
}
