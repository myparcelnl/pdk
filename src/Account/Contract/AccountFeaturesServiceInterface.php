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

    /**
     * The order management version the PDK should behave as.
     *
     * Use this from any code path that adapts behaviour to the order management mode
     * — export dispatch, bulk actions, settings filtering, UI gating, webhook routing.
     * Returns one of the same {@see self::ORDER_MODE_*} constants as
     * {@see self::getOrderModeVersion()}, but the value may diverge from the raw mode
     * once business rules layer on top.
     *
     * Reserve {@see self::getOrderModeVersion()} for code that genuinely needs the raw
     * IAM feature value (display, diagnostics, or behaviour that must follow IAM
     * regardless of plugin-side business rules).
     *
     * @TODO INT-1590: implementations will downgrade ORDER_MODE_V2 to
     *       ORDER_MODE_SHIPMENTS when the customer has no active sales channel,
     *       so manual shipment export keeps working without callers needing
     *       per-mode branches.
     *
     * @return int
     */
    public function getEffectiveOrderMode(): int;
}
