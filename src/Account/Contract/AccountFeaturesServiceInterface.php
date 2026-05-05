<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

interface AccountFeaturesServiceInterface
{
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
     *   0 — no order mode; shop uses shipments (fallback)
     *   1 — Order v1 (LEGACY_ORDER_MANAGEMENT)
     *   2 — Order v2 (ORDER_MANAGEMENT); wins over v1 when both present
     *
     * @return int
     */
    public function getOrderModeVersion(): int;
}
