<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Exception;

use Exception;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryExceptionInterface;
use Throwable;

final class NotImplementedException extends Exception implements FactoryExceptionInterface
{
    public function __construct($message = 'Not implemented', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
