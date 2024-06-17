<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Contract\InternationalMailboxServiceInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @method static bool internationalMailboxPossible(Carrier $carrier)
 * @method static bool isInternationalMailbox(?string $cc, string $packageTypeName)
 * @see \MyParcelNL\Pdk\Base\Contract\InternationalMailboxServiceInterface
 */
class InternationalMailbox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InternationalMailboxServiceInterface::class;
    }
}
