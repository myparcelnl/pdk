<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Product\Repository\AbstractProductRepository;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use RuntimeException;

class MockProductRepository extends AbstractProductRepository
{
    private const DEFAULT_PRODUCTS = [
        [
            'sku'      => '123',
            'weight'   => 4000,
            'settings' => [
                ProductSettings::ALLOW_ONLY_RECIPIENT     => false,
                ProductSettings::ALLOW_SIGNATURE          => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '1234',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ProductSettings::RETURN_SHIPMENTS         => false,
            ],
        ],
        [
            'sku'      => '456',
            'weight'   => 5000,
            'settings' => [
                ProductSettings::ALLOW_ONLY_RECIPIENT     => false,
                ProductSettings::ALLOW_SIGNATURE          => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '4321',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ProductSettings::RETURN_SHIPMENTS         => false,
            ],
        ],
        [
            'sku'      => '789',
            'weight'   => 6000,
            'settings' => [
                ProductSettings::ALLOW_ONLY_RECIPIENT     => false,
                ProductSettings::ALLOW_SIGNATURE          => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '666',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                ProductSettings::RETURN_SHIPMENTS         => false,
            ],
        ],
    ];

    /**
     * @var \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    private $products;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    private $saved;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     */
    public function __construct(MemoryCacheStorage $storage)
    {
        parent::__construct($storage);

        $this->saved    = new PdkProductCollection(self::DEFAULT_PRODUCTS);
        $this->products = $this->getFromStorage();
    }

    /**
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    public function getFromStorage(): PdkProductCollection
    {
        return $this->saved;
    }

    public function getProduct($identifier): PdkProduct
    {
        $result = $this->products->where('sku', '===', $identifier)->first;

        if (null === $result) {
            throw new RuntimeException('No product found for identifier ' . $identifier);
        }

        return $result->first();
    }

    /**
     * @param  mixed $identifier
     *
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    public function getProductSettings($identifier): ProductSettings
    {
        return $this->getProduct($identifier)['settings'];
    }

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        $foundProducts = [];

        foreach ($identifiers as $identifier) {
            $result = $this->getProduct($identifier);

            $foundProducts[] = $result;
        }

        return new PdkProductCollection($foundProducts);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return $this
     */
    public function set(PdkProduct $product): self
    {
        $this->products[] = $product;
        return $this;
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function update(PdkProduct $product): void
    {
        $this->saved[] = $product->toArray();
    }
}
