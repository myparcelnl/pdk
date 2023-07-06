<?php
/** @noinspection StaticClosureCanBeUsedInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());
it('gets translations', function () {
    expect(Language::getTranslations())->toBeArray();
});

it('translates strings', function () {
    expect(Language::translate('apple_tree'))->toBe('Appelboom');
});