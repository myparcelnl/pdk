<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Service;

interface LanguageServiceInterface
{
    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @param  null|string $language
     *
     * @return array<string, string>
     */
    public function getTranslations(?string $language = null): array;

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return string
     */
    public function translate(string $key, ?string $language = null): string;
}
