<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetAccountsResponse extends ApiResponseWithBody
{
    private ?Account $account = null;

    public function getAccount(): Account
    {
        return $this->account;
    }

    protected function parseResponseBody(): void
    {
        $data  = json_decode($this->getBody(), true)['data']['accounts'][0];
        $shops = array_map(static fn(array $shop) => [
            'id'              => $shop['id'] ?? null,
            'accountId'       => $shop['account_id'] ?? null,
            'platformId'      => $shop['platform_id'] ?? null,
            'name'            => $shop['name'] ?? null,
            'hidden'          => $shop['hidden'] ?? false,
            'billing'         => $shop['billing'] ?? null,
            'deliveryAddress' => $shop['delivery_address'] ?? null,
            'generalSettings' => $shop['general_settings'] ?? null,
            'return'          => $shop['return'] ?? null,
            'shipmentOptions' => $shop['shipment_options'] ?? null,
            'trackTrace'      => $shop['track_trace'] ?? null,
        ], $data['shops'] ?? []);

        $this->account = new Account([
            'id'              => $data['id'] ?? null,
            'platformId'      => $data['platform_id'] ?? null,
            'status'          => $data['status'] ?? null,
            'contactInfo'     => $data['contact'] ?? null,
            'generalSettings' => $data['general_settings'] ?? null,
            'shops'           => $shops,
        ]);
    }
}
