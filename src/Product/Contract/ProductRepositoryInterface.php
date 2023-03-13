<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Product\Contract;

use MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

interface ProductRepositoryInterface
{
    /**
     * Get a product by identifier.
     */
    public function getProduct($identifier): PdkProduct;

    /**
     * Get product settings by identifier.
     */
    public function getProductSettings($identifier): ProductSettings;

    /**
     * Get multiple products by identifiers.
     */
    public function getProducts(array $identifiers = []): PdkProductCollection;

    /**
     * Save a product and/or its settings to the repository.
     */
    public function update(PdkProduct $product): void;
}
