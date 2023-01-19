<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;

/**
 * @method static string renderDeliveryOptions(PdkCart $cart)
 * @method static string renderInitScript()
 * @method static string renderModals()
 * @method static string renderNotifications()
 * @method static string renderOrderCard(PdkOrder $order)
 * @method static string renderOrderListColumn(PdkOrder $order)
 * @method static string renderPluginSettings()
 * @method static string renderProductSettings(PdkProduct $product)
 * @implements RenderServiceInterface
 */
class RenderService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RenderServiceInterface::class;
    }
}
