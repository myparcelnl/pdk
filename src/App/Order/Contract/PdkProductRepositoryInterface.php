<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Contract\PdkMultipleRepositoryInterface;

interface PdkProductRepositoryInterface extends PdkMultipleRepositoryInterface
{
    /**
     * Get a single product.
     */
    public function get($input): PdkProduct;

    /**
     * Get multiple products.
     */
    public function getMany($input): PdkProductCollection;

    /**
     * Get a product by identifier.
     *
     * @deprecated Use get() instead. Will be removed in v3.0.0.
     * @TODO       Remove in v3.0.0
     */
    public function getProduct($identifier): PdkProduct;

    /**
     * Get multiple products by identifiers.
     *
     * @deprecated Use getMany() instead. Will be removed in v3.0.0.
     * @TODO       Remove in v3.0.0
     */
    public function getProducts(array $identifiers = []): PdkProductCollection;

    /**
     * Save a product and/or its settings to the repository.
     */
    public function update(PdkProduct $product): void;
}
