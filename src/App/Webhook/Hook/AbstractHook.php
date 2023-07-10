<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHook implements HookInterface
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function handle(Request $request): void
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function validate(Request $request): bool
    {
        return true;
    }
}
