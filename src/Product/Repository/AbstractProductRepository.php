<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Product\Repository;

use MyParcelNL\Pdk\Base\Repository\BaseRepository;
use MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

abstract class AbstractProductRepository extends BaseRepository
{
    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkProduct
     */
    abstract public function getProduct($identifier): PdkProduct;

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    abstract public function getProducts(array $identifiers): PdkProductCollection;

    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    abstract public function getProductSettings($identifier): ProductSettings;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return void
     */
    abstract public function store(PdkProduct $product): void;
}
