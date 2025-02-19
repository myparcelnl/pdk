<?php

/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Service;

use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;

use function DI\get;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesEachMockPdkInstance([
        LanguageServiceInterface::class => get(MockAbstractLanguageService::class),
    ])
);

dataset('languages', [
    ['language' => 'en-GB', 'translation' => 'Send help', 'iso2' => 'en'],
    ['language' => 'nl-NL', 'translation' => 'Stuur hulp', 'iso2' => 'nl'],
    // Use current language, which is 'en' in MockAbstractLanguageService.
    ['language' => null, 'translation' => 'Send help', 'iso2' => 'en'],
]);

it('gets current language', function () {
    expect(Language::getLanguage())->toBe('en-GB');
});

it('loads translations from file', function (?string $language) {
    expect(Language::getTranslations($language))->toBeArray();
})->with('languages');


it('loads fallback translations from file if language is not supported', function () {
    expect(Language::getTranslations('tr-TR'))->toBeArray(['send_help' => 'Send help']);
});


it('translates strings in current language', function (?string $language, string $translation) {
    if ($language) {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService $languageService */
        $languageService = Pdk::get(LanguageServiceInterface::class);
        $languageService->setLanguage($language);
    }

    expect(Language::translate('send_help'))->toBe($translation);
})->with('languages');

it('translates strings in given language', function (?string $language, string $translation) {
    expect(Language::translate('send_help', $language))->toBe($translation);
})->with('languages');

it('ignores missing strings', function () {
    expect(Language::translate('help_is_coming'))->toBe('help_is_coming');
});

it('checks if a translation exists', function () {
    expect(Language::hasTranslation('send_help'))
        ->toBeTrue()
        ->and(Language::hasTranslation('help_is_coming'))
        ->toBeFalse();
});

it('converts ietf code to iso code', function (string $ietf, string $iso) {
    expect(Language::getIso2($ietf))->toBe($iso);
})->with([
    ['en-GB', 'en'],
    ['nl-NL', 'nl'],
    ['tr-TR', 'tr'],
    ['fr-BE', 'fr'],
]);

it('uses fallback language when unsupported language is used', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService $languageService */
    $languageService = Pdk::get(LanguageServiceInterface::class);
    $languageService->setLanguage('tr-TR');

    expect(Language::getIso2())
        ->toBe('en')
        ->and(Language::translate('send_help'))
        ->toBe('Send help');
});

it('translates indexed arrays', function () {
    $result = Language::translateArray(['send_help', 'help_is_coming']);
    expect($result)
        ->toBeArray()
        ->and($result)
        ->toBe([
            'send_help'      => 'Send help',
            'help_is_coming' => 'help_is_coming',
        ]);
});

it('translates associative arrays', function () {
    $result = Language::translateArray([
        'string_1' => 'send_help',
        'string_2' => 'help_is_coming',
        'string_3' => 'i_am_trapped',
        'string_4' => null,
    ]);

    expect($result)
        ->toBeArray()
        ->and($result)
        ->toBe([
            'string_1' => 'Send help',
            'string_2' => 'help_is_coming',
            'string_3' => 'I am stuck',
            'string_4' => null,
        ]);
});

it('translates nested associative arrays', function () {
    $result = Language::translateArray([
        'string_1'  => 'send_help',
        'string_2'  => 'help_is_coming',
        'send_help' => [
            'string_3'       => 'send_help',
            'help_is_coming' => 'help_is_coming',
            'i_am_trapped'   => 'i_am_trapped',
            'string_4'       => null,
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
                'string_4'       => null,
            ],
        ]);
});
