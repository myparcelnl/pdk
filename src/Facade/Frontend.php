<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;

/**
 * @method static string renderChildProductSettings(PdkProduct $product)
 * @method static string renderDeliveryOptions(PdkCart $cart)
 * @method static string renderInitScript()
 * @method static string renderModals()
 * @method static string renderNotifications()
 * @method static string renderOrderBox(PdkOrder $order)
 * @method static string renderOrderListItem(PdkOrder $order)
 * @method static string renderPluginSettings()
 * @method static string renderProductSettings(PdkProduct $product)
 * @implements FrontendRenderServiceInterface
 */
final class Frontend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FrontendRenderServiceInterface::class;
    }
}
