<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Util;

it('returns just the min when min equals max', function () {
    expect(InsuranceTierMath::buildTiers(100, 100))->toEqual([100]);
});

it('returns just the min when min exceeds max', function () {
    expect(InsuranceTierMath::buildTiers(500, 100))->toEqual([500]);
});

it('builds a tier ladder including floor tiers and €500 steps', function () {
    $tiers = InsuranceTierMath::buildTiers(0, 200_000);

    expect($tiers)->toEqual([0, 10_000, 25_000, 50_000, 100_000, 150_000, 200_000]);
});

it('skips floor tiers below the min', function () {
    $tiers = InsuranceTierMath::buildTiers(30_000, 100_000);

    expect($tiers)->toEqual([30_000, 50_000, 100_000]);
});

it('returns unique sorted values when min coincides with a floor tier', function () {
    $tiers = InsuranceTierMath::buildTiers(50_000, 150_000);

    expect($tiers)->toEqual([50_000, 100_000, 150_000]);
});
