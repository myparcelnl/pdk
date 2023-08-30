<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\App\Api\PdkActions;
use MyParcelNL\Pdk\Base\Facade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static Response execute(string|Request $action, array $parameters = [])
 * @see PdkActions
 */
final class Actions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdkActions::class;
    }
}
