<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

use MyParcelNL\Pdk\Carrier\Model\Carrier;

interface InternationalMailboxServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    public function internationalMailboxPossible(Carrier $carrier): bool;

    /**
     * @param  null|string $cc
     * @param  string      $packageTypeName
     *
     * @return bool
     */
    public function isInternationalMailbox(?string $cc, string $packageTypeName): bool;
}
