<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());
it('gets translations', function () {
    expect(LanguageService::getTranslations())->toBeArray();
});

it('translates strings', function () {
    expect(LanguageService::translate('apple_tree'))->toBe('Appelboom');
});
