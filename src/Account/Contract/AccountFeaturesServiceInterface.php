<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

interface AccountFeaturesServiceInterface
{
    public const ORDER_MODE_SHIPMENTS = 0;

    public const ORDER_MODE_V1        = 1;

    public const ORDER_MODE_V2        = 2;

    /**
     * @return bool
     */
    public function canUseOrderNotes(): bool;

    /**
     * @return bool
     */
    public function canUseDirectPrinting(): bool;

    /**
     * @return bool
     */
    public function canUseMyReturns(): bool;

    /**
     * Whether the account uses any order management mode (v1 or v2).
     *
     * @return bool
     */
    public function usesOrderMode(): bool;

    /**
     * The order management version in use.
     *
     * Returns:
     *   self::ORDER_MODE_SHIPMENTS — no order mode; shop uses shipments (fallback)
     *   self::ORDER_MODE_V1 — Order v1 (LEGACY_ORDER_MANAGEMENT)
     *   self::ORDER_MODE_V2 — Order v2 (ORDER_MANAGEMENT); wins over v1 when both present
     *
     * @return int
     */
    public function getOrderModeVersion(): int;
}
