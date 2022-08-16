<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Exception;

use Exception;
use Throwable;

class InvalidCastException extends Exception
{
    /**
     * @param  string          $key
     * @param  mixed           $castType
     * @param  mixed           $arguments
     * @param  null|\Throwable $exception
     */
    public function __construct(string $key, $castType, $arguments = null, Throwable $exception = null)
    {
        $this->message = sprintf(
            'Failed to cast "%s" to "%s"%s.%s',
            $key,
            $castType,
            $arguments ? sprintf(' with the following arguments: %s', json_encode($arguments)) : '',
            $exception ? sprintf(' Reason: %s', $exception->getMessage()) : ''
        );

        parent::__construct();
    }
}
