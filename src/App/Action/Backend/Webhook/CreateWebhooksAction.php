<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateWebhooksAction extends AbstractWebhooksAction
{
    public function handle(Request $request): Response
    {
        $url   = $this->getWebhookUrl($request);
        $hooks = $this->getRequestHooks($request);

        $collection = new WebhookSubscriptionCollection(
            array_map(static fn(string $hook) => compact('hook', 'url'), $hooks)
        );

        $result = $this->repository->subscribeMany($collection);

        $this->pdkWebhooksRepository->store($result);

        return Actions::execute(PdkBackendActions::FETCH_WEBHOOKS);
    }

    /**
     * @return string[]
     */
    private function getRequestHooks(Request $request): array
    {
        return explode(',', (string) $request->get('hooks', [])) ?: [];
    }

    private function getWebhookUrl(Request $request): ?string
    {
        $url = $this->pdkWebhooksRepository->getHashedUrl();

        if (! $url || $request->get('refresh', false)) {
            $url = $this->webhookActions->createUrl();

            $this->pdkWebhooksRepository->storeHashedUrl($url);
        }

        return $url;
    }
}
