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
     */
    abstract public function getProduct($identifier): PdkProduct;

    /**
     * @param  mixed $identifier
     */
    abstract public function getProductSettings($identifier): ProductSettings;

    abstract public function getProducts(array $identifiers = []): PdkProductCollection;

    abstract public function update(PdkProduct $product): void;
}
