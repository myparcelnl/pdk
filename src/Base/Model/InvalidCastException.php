<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use Exception;

class InvalidCastException extends Exception
{
    /**
     * @param  string $key
     * @param  mixed  $castType
     * @param         $arguments
     */
    public function __construct(string $key, $castType, $arguments = null)
    {
        $this->message = sprintf(
            "Failed to cast '%s' to '%s'%s",
            $key,
            $castType,
            $arguments ? sprintf(' with the following arguments: %s', json_encode($arguments)) : ''
        );

        parent::__construct();
    }
}
