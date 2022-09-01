<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;

/**
 * @method static string renderInitScript()
 * @method static string renderModals()
 * @method static string renderNotifications()
 * @method static string renderOrderCard(PdkOrder $order)
 * @method static string renderOrderListColumn(PdkOrder $order)
 * @implements RenderServiceInterface
 */
class RenderService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RenderServiceInterface::class;
    }
}
