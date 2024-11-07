<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

class Str extends \Illuminate\Support\Str
{
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
}
