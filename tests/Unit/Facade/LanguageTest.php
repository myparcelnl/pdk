<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

it('gets translations', function () {
    expect(Language::getTranslations())->toBeArray();
});

it('translates strings', function () {
    expect(Language::translate('apple_tree'))->toBe('Appelboom');
});
