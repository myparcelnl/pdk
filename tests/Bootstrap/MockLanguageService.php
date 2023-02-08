<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;

class MockLanguageService implements LanguageServiceInterface
{
    private const TRANSLATIONS = [
        'apple_tree' => 'Appelboom',
    ];

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
        return self::TRANSLATIONS;
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
     * @param  string      $key
     * @param  null|string $language
     *
     * @return string
     */
    public function translate(string $key, ?string $language = null): string
    {
        return self::TRANSLATIONS[$key];
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
