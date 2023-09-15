<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHook implements HookInterface
{
    abstract protected function getHookEvent(): string;

    public function handle(Request $request): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function validate(Request $request): bool
    {
        return $this->eventMatches($request, $this->getHookEvent());
    }

    protected function eventMatches(Request $request, string $hook): bool
    {
        $content = $this->getHookBody($request);

        return $request->headers->get('x-myparcel-hook') === $hook && $content['event'] === $hook;
    }

    protected function getHookBody(Request $request): array
    {
        $body = json_decode($request->getContent(), true);

        return $body['data']['hooks'][0] ?? [];
    }
}
