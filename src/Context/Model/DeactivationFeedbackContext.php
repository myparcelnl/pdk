<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * Context for the modal that asks for the reason of deactivation in the plugins overview.
 *
 * @property null|string                                                  $linkSelector
 * @property array{value: string, label: string, hasDetails: bool}[]      $reasons
 * @property array<string, string>                                        $translations
 */
class DeactivationFeedbackContext extends Model
{
    /**
     * Used until the translation keys exist in the translation sheet.
     */
    private const FALLBACK_TRANSLATIONS = [
        'deactivation_feedback_title'                    => 'Why do you deactivate MyParcel?',
        'deactivation_feedback_description'              => 'Your answer helps us to improve the plugin. This is optional.',
        'deactivation_feedback_details_placeholder'      => 'Tell us more (optional)',
        'deactivation_feedback_submit'                   => 'Submit and deactivate',
        'deactivation_feedback_skip'                     => 'Skip and deactivate',
        'deactivation_feedback_cancel'                   => 'Cancel',
        'deactivation_feedback_reason_no_longer_needed'  => 'I no longer need the plugin',
        'deactivation_feedback_reason_switched_provider' => 'I switched to a different shipping provider',
        'deactivation_feedback_reason_missing_feature'   => 'A feature that I need is missing',
        'deactivation_feedback_reason_not_working'       => 'The plugin does not work correctly',
        'deactivation_feedback_reason_temporary'         => 'I deactivate it temporarily',
        'deactivation_feedback_reason_other'             => 'Other',
    ];

    public $attributes = [
        'linkSelector' => null,
        'reasons'      => [],
        'translations' => [],
    ];

    protected $casts = [
        'linkSelector' => 'string',
        'reasons'      => 'array',
        'translations' => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['linkSelector'] = Pdk::get('deactivationFeedbackLinkSelector');
        $this->attributes['reasons']      = $this->createReasons();
        $this->attributes['translations'] = [
            'title'              => $this->translate('deactivation_feedback_title'),
            'description'        => $this->translate('deactivation_feedback_description'),
            'detailsPlaceholder' => $this->translate('deactivation_feedback_details_placeholder'),
            'submit'             => $this->translate('deactivation_feedback_submit'),
            'skip'               => $this->translate('deactivation_feedback_skip'),
            'cancel'             => $this->translate('deactivation_feedback_cancel'),
        ];
    }

    /**
     * @return array{value: string, label: string, hasDetails: bool}[]
     */
    private function createReasons(): array
    {
        $reasons = [];

        foreach (Pdk::get('deactivationFeedbackReasons') as $value => $hasDetails) {
            $reasons[] = [
                'value'      => $value,
                'label'      => $this->translate("deactivation_feedback_reason_$value"),
                'hasDetails' => (bool) $hasDetails,
            ];
        }

        return $reasons;
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    private function translate(string $key): string
    {
        if (Language::hasTranslation($key)) {
            return Language::translate($key);
        }

        return self::FALLBACK_TRANSLATIONS[$key] ?? $key;
    }
}
