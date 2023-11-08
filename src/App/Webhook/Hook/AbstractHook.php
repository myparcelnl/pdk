<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHook implements HookInterface
{
    abstract protected function getHookEvent(): string;

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

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function getHookBody(Request $request): array
    {
        $body = json_decode($request->getContent(), true);

        return $body['data']['hooks'][0] ?? [];
    }
}
