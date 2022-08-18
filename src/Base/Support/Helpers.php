<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Sdk\src\Support\Arr;

class Helpers extends \MyParcelNL\Sdk\src\Support\Helpers
{
    /**
     * @param  mixed $target
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return array|mixed
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function data_get($target, $key, $default = null)
    {
        if (null === $key) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if (null === $segment) {
                return $target;
            }

            if ('*' === $segment) {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return $this->value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = $this->data_get($item, $key);
                }

                return in_array('*', $key, true) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $this->value($default);
            }
        }

        return $target;
    }
}
