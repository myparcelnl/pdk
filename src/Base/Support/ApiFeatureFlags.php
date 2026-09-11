<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Facade\Pdk;

/**
 * Turns the configured API feature flags into request headers.
 *
 * The MyParcel APIs expose feature flags as `x-dmp-*` headers. What a flag changes differs per flag,
 * so read up on one before switching it on. Which flags we send is a deployment decision rather than
 * a merchant one, so it lives in config.
 *
 * Call this from any request layer that has to pass the flags along, so there is one list to change
 * when a flag is added or removed.
 *
 * @see https://myparcelnl.atlassian.net/wiki/spaces/MD/pages/12779590/API+feature+flags
 */
final class ApiFeatureFlags
{
    /**
     * The headers to add to an outgoing MyParcel API request.
     *
     * Returns an empty array when nothing is configured, so callers can merge unconditionally.
     *
     * @return array<string, string>
     */
    public static function getHeaders(): array
    {
        /** @var null|string[] $flags */
        $flags = Pdk::get('apiFeatureFlags');

        return array_fill_keys($flags ?? [], 'true');
    }
}
