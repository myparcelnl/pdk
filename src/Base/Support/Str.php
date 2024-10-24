<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Sdk\src\Support\Str as SdkStr;

class Str extends SdkStr
{
    /**
     * @var array
     */
    private static $flagCaseCache = [];

    /**
     * @param  string   $string
     * @param  null|int $flags
     *
     * @return string
     */
    public static function changeCase(string $string, ?int $flags = null): string
    {
        $case = self::getFlagCase($flags);

        return self::{$case}($string);
    }

    /**
     * @param  null|int $flags
     *
     * @return string
     */
    private static function getFlagCase(?int $flags = null): string
    {
        if (! isset(self::$flagCaseCache[$flags])) {
            $case = 'camel';

            if ($flags & Arrayable::CASE_SNAKE) {
                $case = 'snake';
            }

            if ($flags & Arrayable::CASE_KEBAB) {
                $case = 'kebab';
            }

            if ($flags & Arrayable::CASE_STUDLY) {
                $case = 'studly';
            }

            self::$flagCaseCache[$flags] = $case;
        }

        return self::$flagCaseCache[$flags];
    }
}
