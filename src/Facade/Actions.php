<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\Base\Facade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static Response execute(string|Request $action, array $parameters = [])
 * @method static Response executeAutomatic(string|Request $action, array $parameters = [])
 * @see \MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface
 */
final class Actions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdkActionsServiceInterface::class;
    }
}
