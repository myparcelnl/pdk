<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Util;

/**
 * Pure-math helpers for insurance tier ladders.
 *
 * Extracted from CapabilitiesValidationService so the service can stay focused
 * on capability-level queries.
 */
final class InsuranceTierMath
{
    /**
     * Build an insurance-tier ladder for a min/max range.
     *
     * Includes fine-grained floor tiers (€100, €250, €500) at the low end so that
     * low-value orders can still be insured at realistic increments, then €500
     * steps for higher amounts.
     *
     * @param  int $min Minimum amount in cents
     * @param  int $max Maximum amount in cents
     *
     * @return int[] Sorted, unique tier amounts in cents, including min and max
     */
    public static function buildTiers(int $min, int $max): array
    {
        if ($min >= $max) {
            return [$min];
        }

        $tiers = [$min];

        // Floor tiers between min and max (exclusive bounds): €100, €250, €500.
        foreach ([10_000, 25_000, 50_000] as $tier) {
            if ($tier > $min && $tier < $max) {
                $tiers[] = $tier;
            }
        }

        // €500 steps from the next round €500 boundary up to (but excluding) max.
        $stepStart = max($min, 50_000) + 50_000;
        for ($t = $stepStart; $t < $max; $t += 50_000) {
            $tiers[] = $t;
        }

        $tiers[] = $max;

        return array_values(array_unique($tiers));
    }
}
