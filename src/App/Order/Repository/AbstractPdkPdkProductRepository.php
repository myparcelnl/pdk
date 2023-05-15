<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

abstract class AbstractPdkPdkProductRepository extends Repository implements PdkProductRepositoryInterface
{
    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
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
     */
    abstract public function getProducts(array $identifiers = []): PdkProductCollection;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return void
     */
    abstract public function update(PdkProduct $product): void;
}
