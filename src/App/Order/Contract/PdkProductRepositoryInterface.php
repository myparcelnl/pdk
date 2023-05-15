<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

interface PdkProductRepositoryInterface
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
