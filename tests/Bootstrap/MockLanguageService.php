<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;

class MockLanguageService implements LanguageServiceInterface
{
    private const TRANSLATIONS = [
        'apple_tree' => 'Appelboom',
    ];

    /**
     * @param  null|string $language
     */
    public function getIso2(?string $language = null): string
    {
        return substr($language ?? $this->getLanguage(), 0, 2);
    }

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
     * @param  null|string $language
     */
    public function hasTranslation(string $key, ?string $language = null): bool
    {
        return true;
    }

    /**
     * @param  null|string $language
     */
    public function translate(string $key, ?string $language = null): string
    {
        return self::TRANSLATIONS[$key] ?? $key;
    }

    /**
     * @param  null|string $language
     */
    public function translateArray(array $array, ?string $language = null): array
    {
        return array_map(fn($value) => $this->translate($value, $language), $array);
    }
}
