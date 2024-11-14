<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

class Str extends \Illuminate\Support\Str
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
     * @param  string $value
     * @param  int    $limit
     * @param  string $end
     *
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...'): string
    {
        return parent::limit($value, $limit - strlen($end), $end);
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
