<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesEachMockPdkInstance([
            LanguageServiceInterface::class => autowire(MockAbstractLanguageService::class),
        ]
    )
);

dataset('languages', [
    ['language' => 'en-GB', 'translation' => 'Send help', 'iso2' => 'en'],
    ['language' => 'nl-NL', 'translation' => 'Stuur hulp', 'iso2' => 'nl'],
    // Use current language, which is 'en' in MockAbstractLanguageService.
    ['language' => null, 'translation' => 'Send help', 'iso2' => 'en'],
]);

it('gets current language', function () {
    expect(LanguageService::getLanguage())->toBe('en-GB');
});

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

it('checks if a translation exists', function () {
    expect(LanguageService::hasTranslation('send_help'))
        ->toBeTrue()
        ->and(LanguageService::hasTranslation('help_is_coming'))
        ->toBeFalse();
});

it('converts to iso2 language code', function (?string $language, string $translation, string $iso2) {
    expect(LanguageService::getIso2($language))->toBe($iso2);
})->with('languages');

it('translates indexed arrays', function () {
    $result = LanguageService::translateArray(['send_help', 'help_is_coming']);
    expect($result)
        ->toBeArray()
        ->and($result)
        ->toBe([
            'send_help'      => 'Send help',
            'help_is_coming' => 'help_is_coming',
        ]);
});

it('translates associative arrays', function () {
    $result = LanguageService::translateArray([
        'string_1' => 'send_help',
        'string_2' => 'help_is_coming',
        'string_3' => 'i_am_trapped',
    ]);

    expect($result)
        ->toBeArray()
        ->and($result)
        ->toBe([
            'string_1' => 'Send help',
            'string_2' => 'help_is_coming',
            'string_3' => 'I am stuck',
        ]);
});

it('translates nested associative arrays', function () {
    $result = LanguageService::translateArray([
        'string_1'  => 'send_help',
        'string_2'  => 'help_is_coming',
        'send_help' => [
            'string_3'       => 'send_help',
            'help_is_coming' => 'help_is_coming',
            'i_am_trapped'   => 'i_am_trapped',
        ],
    ]);

    expect($result)
        ->toBeArray()
        ->and($result)
        ->toBe([
            'string_1'  => 'Send help',
            'string_2'  => 'help_is_coming',
            'send_help' => [
                'string_3'       => 'Send help',
                'help_is_coming' => 'help_is_coming',
                'i_am_trapped'   => 'I am stuck',
            ],
        ]);
});
