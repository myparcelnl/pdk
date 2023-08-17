<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Exception;

use Exception;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryExceptionInterface;

final class InvalidFactoryException extends Exception implements FactoryExceptionInterface
{
}
