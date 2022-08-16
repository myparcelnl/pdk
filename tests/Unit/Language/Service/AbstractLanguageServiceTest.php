<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(
        MockPdkConfig::create([
            'service.language' => MockAbstractLanguageService::class,
        ])
    );
});

dataset('languages', [
    ['language' => 'en', 'translation' => 'Send help'],
    ['language' => 'nl', 'translation' => 'Stuur hulp'],
    // Use current language, which is 'en' in MockAbstractLanguageService.
    ['language' => null, 'translation' => 'Send help'],
]);

it('loads translations from file', function (?string $language) {
    expect(LanguageService::getTranslations($language))->toBeArray();
})->with('languages');

it('translates strings in current language', function (?string $language, string $translation) {
    if ($language) {
        LanguageService::setLanguage($language);
    }

    expect(LanguageService::translate('send_help'))->toBe($translation);
})->with('languages');

it('translates strings in given language', function (?string $language, string $translation) {
    expect(LanguageService::translate('send_help', $language))->toBe($translation);
})->with('languages');

it('ignores missing strings', function () {
    expect(LanguageService::translate('help_is_coming'))->toBe('help_is_coming');
});
