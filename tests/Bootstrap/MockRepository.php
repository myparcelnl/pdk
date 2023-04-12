<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Account\Request\GetShopRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponse;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

class MockRepository extends ApiRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $values;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage       $storage
     * @param  \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $api
     */
    public function __construct(MemoryCacheStorage $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage, $api);

        $this->values = new Collection([
            'account' => $this->getAccount(),
            'shop'    => $this->getShopWithParameters(3),
        ]);
    }

    /**
     * @param  string $key
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function get(string $key): Collection
    {
        return $this->values->firstWhere('key', $key);
    }

    /**
     * @return \MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(): Account
    {
        return $this->retrieve('account', function () {
            return new Account([
                'id'          => 4,
                'platform_id' => Platform::MYPARCEL_ID,
                'shops'       => (new Collection([
                    [
                        'id'   => 1,
                        'name' => 'Potlodenshop',
                    ],
                    [
                        'id'   => 2,
                        'name' => 'MijnBoekenShop',
                    ],
                ])),
            ]);
        });
    }

    /**
     * @param  int $shopId
     *
     * @return \MyParcelNL\Pdk\Account\Model\Shop
     */
    public function getShopWithParameters(int $shopId): Shop
    {
        return $this->retrieve('shop', function () use ($shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopsResponse $response */
            $response = $this->api->doRequest(new GetShopRequest($shopId), GetShopsResponse::class);

            return $response->getShop();
        });
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function mockReturnValue(string $key, $value): void
    {
        $this->values = $this->values
            ->where('key', '!=', $key)
            ->put($key, $value);
    }
}
