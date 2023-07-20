<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Repository\StorageRepository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Sdk\src\Support\Str;

class PdkProductRepository extends StorageRepository implements
    PdkProductRepositoryInterface
{
    public function get($input): PdkProduct
    {
        return $this->retrieve(sprintf('product_%s', $input));
    }

    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getMany($input): PdkProductCollection
    {
        return new PdkProductCollection(
            array_map(function ($identifier) {
                return $this->get($identifier);
            }, Utils::toArray($input))
        );
    }

    /**
     * @param $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     * @deprecated Use get() instead
     */
    public function getProduct($identifier): PdkProduct
    {
        Logger::reportDeprecatedMethod(__METHOD__, 'get');
        return $this->get($identifier);
    }

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     * @deprecated Use getMany() instead
     */
    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        Logger::reportDeprecatedMethod(__METHOD__, 'getMany');
        return $this->getMany($identifiers);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return void
     */
    public function update(PdkProduct $product): void
    {
        $this->save(sprintf('product_%s', $product->externalIdentifier), $product);
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function transformData(string $key, $data)
    {
        if (Str::startsWith($key, 'product_settings_')) {
            return $data ? new ProductSettings($data) : null;
        }

        if (Str::startsWith($key, 'product_')) {
            return $data ? new PdkProduct($data) : null;
        }

        return parent::transformData($key, $data);
    }
}
