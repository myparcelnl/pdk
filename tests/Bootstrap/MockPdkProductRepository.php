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
            'name'               => 'Pear',
            'weight'             => 4000,
            'settings'           => [
                ProductSettings::CUSTOMS_CODE   => '1234',
                ProductSettings::DROP_OFF_DELAY => 0,
                ProductSettings::FIT_IN_MAILBOX => 0,
            ],
        ],
        [
            'externalIdentifier' => '456',
            'name'               => 'Apple',
            'sku'                => 'A-456',
            'ean'                => '212444',
            'weight'             => 5000,
            'settings'           => [
                ProductSettings::CUSTOMS_CODE => '4321',
                ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
        ],
        [
            'externalIdentifier' => '789',
            'name'               => 'Banana',
            'sku'                => 'A-789',
            'weight'             => 6000,
            'settings'           => [
                ProductSettings::CUSTOMS_CODE => '666',
                ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
            ],
        ],
    ];

    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    private                      $products;

    private PdkProductCollection $saved;

    public function __construct(MemoryCacheStorage $storage)
    {
        parent::__construct($storage);
        $this->reset();
    }

    public function add(PdkProductCollection $products): void
    {
        $this->products = $this->products->merge($products);
    }

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
     */
    public function getProductSettings($identifier): ProductSettings
    {
        return $this->getProduct($identifier)['settings'];
    }

    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        if (empty($identifiers)) {
            return $this->products;
        }

        return $this->products->whereIn('externalIdentifier', $identifiers);
    }

    public function reset(array $products = self::DEFAULT_PRODUCTS): void
    {
        $this->saved    = new PdkProductCollection($products);
        $this->products = $this->getFromStorage();
    }

    /**
     * @param  mixed $data
     *
     * @return mixed
     */
    public function save(string $key, $data)
    {
        $this->products->push($data);

        return parent::save($key, $data);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function update(PdkProduct $product): void
    {
        $this->saved->firstWhere('externalIdentifier', $product->externalIdentifier)
            ->fill($product->toArray());
    }
}
