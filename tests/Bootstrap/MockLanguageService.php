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
     * @return string
     */
    public function getLanguage(): string
    {
        return 'nl';
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
     * @return string
     */
    public function translate(string $key, ?string $language = null): string
    {
        return self::TRANSLATIONS[$key];
    }
}
