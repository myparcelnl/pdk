<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * This service ignores missing translations and just returns the key, unlike the MockAbstractLanguageService. Use this
 * service when you need to see all possible translations, rather than the ones that are actually set. For example, in
 * the settings page, where descriptions are not rendered if there is no translation for them.
 *
 * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService
 */
class MockLanguageService implements LanguageServiceInterface, ResetInterface
{
    private const TRANSLATIONS = [
        'apple_tree'                     => 'Appelboom',
        'delivery_options'               => 'Delivery options',
        'delivery_options_morning'       => 'Ochtend',
        'some_delivery_options_broccoli' => 'Broccoli',
    ];

    /**
     * @var string[]
     */
    private $translations = [];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * @param  null|string $language
     *
     * @return string
     */
    public function getIso2(?string $language = null): string
    {
        return substr($language ?? $this->getLanguage(), 0, 2);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return 'nl-NL';
    }

    /**
     * @return string[]
     */
    public function getTranslations(?string $language = null): array
    {
        return $this->translations;
    }

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return bool
     */
    public function hasTranslation(string $key, ?string $language = null): bool
    {
        return true;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->setTranslations(self::TRANSLATIONS);
    }

    /**
     * @param  array $translations
     *
     * @return void
     */
    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return string
     */
    public function translate(string $key, ?string $language = null): string
    {
        return $this->getTranslations()[$key] ?? $key;
    }

    /**
     * @param  array       $array
     * @param  null|string $language
     *
     * @return array
     */
    public function translateArray(array $array, ?string $language = null): array
    {
        return array_map(function ($value) use ($language) {
            return $this->translate($value, $language);
        }, $array);
    }
}
