<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\HookInterface;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
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
        return $this->eventMatches($request, WebhookSubscription::SHIPMENT_STATUS_CHANGE);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                    $hook
     *
     * @return bool
     */
    protected function eventMatches(Request $request, string $hook): bool
    {
        $content = $this->getHookBody($request);

        return $request->headers['x-myparcel-hook'] === $hook && $content['event'] === $hook;
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
