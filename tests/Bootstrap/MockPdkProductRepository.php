<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkPdkProductRepository;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use RuntimeException;

class MockPdkProductRepository extends AbstractPdkPdkProductRepository
{
    private const DEFAULT_PRODUCTS = [
        [
            'externalIdentifier' => '123',
            'weight'             => 4000,
            'settings'           => [
                ProductSettings::EXPORT_ONLY_RECIPIENT    => false,
                ProductSettings::EXPORT_SIGNATURE         => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '1234',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ProductSettings::EXPORT_RETURN            => false,
            ],
        ],
        [
            'sku'      => '456',
            'weight'   => 5000,
            'settings' => [
                ProductSettings::EXPORT_ONLY_RECIPIENT    => false,
                ProductSettings::EXPORT_SIGNATURE         => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '4321',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ProductSettings::EXPORT_RETURN            => false,
            ],
        ],
        [
            'sku'      => '789',
            'weight'   => 6000,
            'settings' => [
                ProductSettings::EXPORT_ONLY_RECIPIENT    => false,
                ProductSettings::EXPORT_SIGNATURE         => false,
                ProductSettings::COUNTRY_OF_ORIGIN        => 'NL',
                ProductSettings::CUSTOMS_CODE             => '666',
                ProductSettings::DISABLE_DELIVERY_OPTIONS => false,
                ProductSettings::DROP_OFF_DELAY           => 0,
                ProductSettings::EXPORT_AGE_CHECK         => false,
                ProductSettings::EXPORT_INSURANCE         => false,
                ProductSettings::EXPORT_LARGE_FORMAT      => false,
                ProductSettings::FIT_IN_MAILBOX           => 0,
                ProductSettings::PACKAGE_TYPE             => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                ProductSettings::EXPORT_RETURN            => false,
            ],
        ],
    ];

    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    private $products;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    private $saved;

    /**
     * @param  array                                      $products
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     */
    public function __construct(array $products = self::DEFAULT_PRODUCTS, MemoryCacheStorage $storage)
    {
        parent::__construct($storage);

        $this->saved    = new PdkProductCollection($products);
        $this->products = $this->getFromStorage();
    }

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getFromStorage(): PdkProductCollection
    {
        return $this->saved;
    }

    public function getProduct($identifier): PdkProduct
    {
        $product = $this->products->firstWhere('externalIdentifier', $identifier);

        if (! $product) {
            throw new RuntimeException(sprintf("No product found for identifier '%s'", $identifier));
        }

        return $product;
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
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        if (empty($identifiers)) {
            return $this->products;
        }

        return $this->products->whereIn('externalIdentifier', $identifiers);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function update(PdkProduct $product): void
    {
        $this->saved->firstWhere('externalIdentifier', $product->externalIdentifier)
            ->fill($product->toArray());
    }
}
