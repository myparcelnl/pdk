<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;

/**
 * @method static string getIso2(string $language = null)
 * @method static string getLanguage()
 * @method static array<string, string> getTranslations(string $language = null)
 * @method static string translate(string $string, string $language = null)
 * @method static array translateArray(array $array, string $language = null)
 * @method static string hasTranslation(string $string, string $language = null)
 * @see \MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface
 */
final class Language extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LanguageServiceInterface::class;
    }
}
