<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Account\Request\GetShopsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;
use MyParcelNL\Sdk\src\Model\Account\Shop;
use MyParcelNL\Sdk\src\Support\Collection;

class MockRepository extends AbstractRepository
{
    /**
     * @var \MyParcelNL\Sdk\src\Support\Collection
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
            'shop'    => $this->getShopWithParameters(),
        ]);
    }

    /**
     * @param  string $key
     *
     * @return \MyParcelNL\Sdk\src\Support\Collection
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
                [
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
                    ]))->mapInto(Shop::class),
                ],
            ]);
        });
    }

    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\Shop
     */
    public function getShopWithParameters(): Shop
    {
        return $this->retrieve('shop', function () {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody $response */
            $response = $this->api->doRequest(new GetShopsRequest(), GetShopsResponseWithBody::class);

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
