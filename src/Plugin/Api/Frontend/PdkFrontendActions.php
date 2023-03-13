<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Frontend;

use MyParcelNL\Pdk\Plugin\Api\Contract\PdkActionsInterface;

final class PdkFrontendActions implements PdkActionsInterface
{
    public const FETCH_CHECKOUT_CONTEXT = 'fetchCheckoutContext';

    /**
     * @return string[]
     */
    public function getActions(): array
    {
        return [
            self::FETCH_CHECKOUT_CONTEXT,
        ];
    }
}
