<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Frontend;

use MyParcelNL\Pdk\Plugin\Api\Contract\PdkActionsInterface;
use MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions;

final class PdkFrontendActions implements PdkActionsInterface
{
    public const FETCH_CHECKOUT_CONTEXT = 'fetchCheckoutContext';

    /**
     * @var \MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions
     */
    private $sharedActions;

    public function __construct(PdkSharedActions $sharedActions)
    {
        $this->sharedActions = $sharedActions;
    }

    public function getActions(): array
    {
        return [
            $this->sharedActions->getActions() + [
                self::FETCH_CHECKOUT_CONTEXT,
            ],
        ];
    }
}
