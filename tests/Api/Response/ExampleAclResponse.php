<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleAclResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'account_id'            => 170402,
            'shop_ids'              => [
                93683,
            ],
            'permissions'           => [
                'selectAccount',
                'selectShop',
                'insertShipment',
                'updateShipment',
                'deleteShipment',
                'selectShipment',
                'insertWebhookSubscription',
                'deleteWebhookSubscription',
                'selectWebhookSubscription',
                'readTrackTrace',
                'printShipmentLabel',
                'fileUploadWrite',
                'accessCollectTrip',
                'ticketCreate',
                'authGetAcl',
                'insertExternalIntegrationProviderCredentials',
                'updateExternalIntegrationProviderCredentials',
                'selectExternalIntegrationProviderCredentials',
                'accountSelectOrder',
                'selectShipmentFromOtherShop',
                'readPriceAmounts',
                'selectCarrierOptionsAccessible',
                'fulfilmentReadProducts',
                'fulfilmentWriteProducts',
                'fulfilmentDeleteProducts',
                'fulfilmentReadOrders',
                'fulfilmentWriteOrders',
                'fulfilmentDeleteOrders',
                'fulfilmentReadOrderNotes',
                'fulfilmentWriteOrderNotes',
                'fulfilmentDeleteOrderNotes',
            ],
            'platform_id'           => 1,
            'user_id'               => 82444,
            'subscription_features' => [
                'allow_custom_contracts',
                'allow_bol_dot_com_vvb_shipments',
                'allow_order_notes',
            ],
        ];
    }
}
