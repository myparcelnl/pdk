<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Account\Request\GetShopRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Sdk\src\Model\Account\Account;
use MyParcelNL\Sdk\src\Model\Account\Shop;

class MockRepository extends AbstractRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $values;

    /**
     * @param  \MyParcelNL\Pdk\Base\Pdk $pdk
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(Pdk $pdk)
    {
        parent::__construct($pdk);

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
     * @return \MyParcelNL\Sdk\src\Model\Account\Account
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
     * @return \MyParcelNL\Sdk\src\Model\Account\Shop
     */
    public function getShopWithParameters(int $shopId): Shop
    {
        return $this->retrieve('shop', function () use ($shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody $response */
            $response = $this->api->doRequest(new GetShopRequest($shopId), GetShopsResponseWithBody::class);

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
