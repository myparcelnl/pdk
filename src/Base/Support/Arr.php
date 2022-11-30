<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Sdk\src\Support\Arr as SdkArr;

class Arr extends SdkArr
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Helpers
     */
    private static $helpers;

    /**
     * @param  array $dotted
     *
     * @return array
     */
    public static function undot(array $dotted): array
    {
        $array = [];

        foreach ($dotted as $key => $value) {
            self::getHelpers()
                ->data_set($array, $key, $value);
        }

        return $array;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Helpers
     */
    protected static function getHelpers(): Helpers
    {
        if (! self::$helpers) {
            self::$helpers = new Helpers();
        }

        return self::$helpers;
    }
}
