<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\App\Api\PdkActions;
use MyParcelNL\Pdk\Base\Facade;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static Response execute($action, array $parameters = [])
 * @implements PdkActions
 */
final class Actions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdkActions::class;
    }
}