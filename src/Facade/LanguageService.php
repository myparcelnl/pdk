<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;

/**
 * @method static string getIso2(string $language = null)
 * @method static string getLanguage()
 * @method static array<string, string> getTranslations(string $language = null)
 * @method static string translate(string $string, string $language = null)
 * @method static array translateArray(array $array, string $language = null)
 * @method static string hasTranslation(string $string, string $language = null)
 * @implements \MyParcelNL\Pdk\Language\Service\LanguageServiceInterface
 */
class LanguageService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return LanguageServiceInterface::class;
    }
}
