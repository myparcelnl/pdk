<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Shared;

use MyParcelNL\Pdk\Plugin\Api\Contract\PdkActionsInterface;

final class PdkSharedActions implements PdkActionsInterface
{
    public const FETCH_CONTEXT = 'fetchContext';

    public function getActions(): array
    {
        return [
            self::FETCH_CONTEXT,
        ];
    }
}
