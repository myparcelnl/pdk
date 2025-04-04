<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Contract;

interface LanguageServiceInterface
{
    /**
     * Get iso 2 language code by given language, or $this->getLanguage(). If instance language is used and the language
     * is not supported, a fallback language will be returned.
     *
     * @example en
     * @example nl
     */
    public function getIso2(?string $language = null): string;

    /**
     * Given an ISO-639-1 language code, returns the language code back if supported or a fallback language if not.
     * @param string $isoCode
     * @return string
     */
    public function getLanguageWithFallback(string $isoCode): string;

    /**
     * Returns the IETF language tag of the current language.
     *
     * @example en-US
     * @example nl-NL
     */
    public function getLanguage(): string;

    /**
     * Get an array of translations for the given language. If no language is given, the current language is used.
     *
     * @param  null|string $language
     *
     * @return array<string, string>
     */
    public function getTranslations(?string $language = null): array;

    /**
     * Check if a translation exists for the given key in the given language. If no language is given, the current
     * language is used.
     */
    public function hasTranslation(string $key, ?string $language = null): bool;

    /**
     * Translate a string by key in the given language. Falls back to the current language if no language is given.
     */
    public function translate(string $key, ?string $language = null): string;

    /**
     * Translate an array of strings by key in the given language. Falls back to the current language if no language is
     * given.
     */
    public function translateArray(array $array, ?string $language = null): array;
}
