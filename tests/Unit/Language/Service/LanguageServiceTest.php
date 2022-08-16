<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
});

it('gets translations', function () {
    expect(LanguageService::getTranslations())->toBeArray();
});

it('translates strings', function () {
    expect(LanguageService::translate('apple_tree'))->toBe('Appelboom');
});
