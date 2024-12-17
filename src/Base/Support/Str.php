<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

class Str extends \Illuminate\Support\Str
{
    public const CASE_SNAKE  = 1;
    public const CASE_KEBAB  = 2;
    public const CASE_STUDLY = 4;

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
     * @param  string $value
     * @param  int    $limit
     * @param  string $end
     *
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit - mb_strwidth($end, 'UTF-8'), '', 'UTF-8')) . $end;
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

            if ($flags & self::CASE_SNAKE) {
                $case = 'snake';
            }

            if ($flags & self::CASE_KEBAB) {
                $case = 'kebab';
            }

            if ($flags & self::CASE_STUDLY) {
                $case = 'studly';
            }

            self::$flagCaseCache[$flags] = $case;
        }

        return self::$flagCaseCache[$flags];
    }
}
