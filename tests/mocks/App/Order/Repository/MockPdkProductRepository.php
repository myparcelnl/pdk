<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Contract\MockServiceInterface;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use RuntimeException;

final class MockPdkProductRepository extends AbstractPdkPdkProductRepository implements MockServiceInterface
{
    private const DEFAULT_PRODUCTS = [
        [
            'externalIdentifier' => '123',
            'name'               => 'Pear',
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
            'externalIdentifier' => '456',
            'name'               => 'Apple',
            'sku'                => 'A-456',
            'ean'                => '212444',
            'weight'             => 5000,
            'settings'           => [
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
            'externalIdentifier' => '789',
            'name'               => 'Banana',
            'sku'                => 'A-789',
            'weight'             => 6000,
            'settings'           => [
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
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     */
    public function __construct(MemoryCacheStorage $storage)
    {
        parent::__construct($storage);
        $this->reset();
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection $products
     *
     * @return void
     */
    public function add(PdkProductCollection $products): void
    {
        $this->products = $this->products->merge($products);
    }

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getFromStorage(): PdkProductCollection
    {
        return $this->saved;
    }

    /**
     * @param $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     */
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
     * @param  array $products
     *
     * @return void
     */
    public function reset(array $products = self::DEFAULT_PRODUCTS): void
    {
        $this->saved    = new PdkProductCollection($products);
        $this->products = $this->getFromStorage();
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    public function save(string $key, $data)
    {
        $this->products->push($data);

        return parent::save($key, $data);
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
