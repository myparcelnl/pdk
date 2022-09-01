<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,PhpUndefinedMethodInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\autowire;

beforeEach(function () {
    PdkFactory::create(
        MockPdkConfig::create([
            LanguageServiceInterface::class => autowire(MockAbstractLanguageService::class),
        ])
    );
});

it('loads translations from file', function () {
    expect(LanguageService::getTranslations())->toBeArray();
});

it('translates strings', function () {
    LanguageService::setLanguage('nl');
    expect(LanguageService::translate('send_help'))->toBe('Stuur hulp');

    LanguageService::setLanguage('en');
    expect(LanguageService::translate('send_help'))->toBe('Send help');
});

it('ignores missing strings', function () {
    expect(LanguageService::translate('help_is_coming'))->toBe('help_is_coming');
});
