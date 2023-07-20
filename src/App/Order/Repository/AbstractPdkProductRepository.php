<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;

/**
 * @deprecated Use \MyParcelNL\Pdk\App\Order\Repository\PdkProductRepository instead. Will be removed in v3.0.0.
 * @see        \MyParcelNL\Pdk\App\Order\Repository\PdkProductRepository
 */
abstract class AbstractPdkProductRepository extends Repository implements PdkProductRepositoryInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
     */
    public function __construct(CacheStorageInterface $cache)
    {
        Logger::reportDeprecatedClass(__CLASS__, PdkProductRepository::class);
        parent::__construct($cache);
    }

    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     * @deprecated Use get() instead. Will be removed in v3.0.0.
     */
    abstract public function getProduct($identifier): PdkProduct;

    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    abstract public function getProductSettings($identifier): ProductSettings;

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     * @deprecated Use getMany() instead. Will be removed in v3.0.0.
     */
    abstract public function getProducts(array $identifiers = []): PdkProductCollection;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return void
     */
    abstract public function update(PdkProduct $product): void;

    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     */
    public function get($input): PdkProduct
    {
        return $this->getProduct($input);
    }

    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getMany($input): PdkProductCollection
    {
        return $this->getProducts($input);
    }
}
