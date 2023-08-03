<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;

/**
 * @property null|string                                     $externalIdentifier
 * @property null|string                                     $sku
 * @property null|string                                     $ean
 * @property null|bool                                       $isDeliverable
 * @property null|string                                     $name
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency        $price
 * @property int                                             $weight
 * @property int                                             $length
 * @property int                                             $height
 * @property int                                             $width
 * @property \MyParcelNL\Pdk\Settings\Model\ProductSettings  $settings
 * @property null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $parent
 */
class ProductDataContext extends PdkProduct
{
}
