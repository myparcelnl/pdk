<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\get;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance([
    LanguageServiceInterface::class => get(MockAbstractLanguageService::class),
]));

it('creates a reason per configured reason', function () {
    $context = new DeactivationFeedbackContext();

    expect(array_column($context->reasons, 'value'))
        ->toBe(['no_longer_needed', 'switched_provider', 'missing_feature', 'not_working', 'temporary', 'other'])
        ->and(array_column($context->reasons, 'hasDetails'))
        ->toBe([false, true, true, true, false, true]);
});

it('uses the translations when they exist', function () {
    /** @var MockAbstractLanguageService $languageService */
    $languageService = Pdk::get(LanguageServiceInterface::class);
    $languageService->setTranslations('en', [
        'deactivation_feedback_title'        => 'Waarom stop je?',
        'deactivation_feedback_reason_other' => 'Iets anders',
    ]);

    $context = new DeactivationFeedbackContext();

    expect($context->translations['title'])
        ->toBe('Waarom stop je?')
        ->and($context->reasons[5]['label'])
        ->toBe('Iets anders');

    $languageService->reset();
});

it('uses the fallback texts when the translations do not exist yet', function () {
    $context = new DeactivationFeedbackContext();

    expect($context->translations)
        ->toHaveKeys(['title', 'description', 'detailsPlaceholder', 'submit', 'skip', 'cancel'])
        ->and($context->translations['title'])
        ->toBe('Why do you deactivate MyParcel?')
        ->and($context->reasons[0]['label'])
        ->toBe('I no longer need the plugin');
});

it('passes the link selector of the plugin', function (?string $selector) {
    $reset = mockPdkProperties(['deactivationFeedbackLinkSelector' => $selector]);

    $context = new DeactivationFeedbackContext();
    $reset();

    expect($context->linkSelector)->toBe($selector);
})->with([
    'not set by the plugin' => [null],
    'set by the plugin'     => ['tr[data-plugin^="my-plugin/"] .deactivate a'],
]);
