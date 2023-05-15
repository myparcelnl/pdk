<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Plugin\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

/**
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
class Frontend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FrontendRenderServiceInterface::class;
    }
}
