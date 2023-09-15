<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractWebhooksAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface
     */
    protected $pdkWebhooksRepository;

    /**
     * @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository
     */
    protected $repository;

    /**
     * @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface
     */
    protected $webhookActions;

    public function __construct(
        WebhookSubscriptionRepository  $repository,
        PdkWebhooksRepositoryInterface $pdkWebhooksRepository,
        PdkWebhookServiceInterface     $pdkWebhookActions
    ) {
        $this->repository            = $repository;
        $this->pdkWebhooksRepository = $pdkWebhooksRepository;
        $this->webhookActions        = $pdkWebhookActions;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection $subscriptions
     */
    protected function createResponse(?WebhookSubscriptionCollection $subscriptions = null): JsonResponse
    {
        return new JsonResponse([
            'webhooks' => array_map(static function (string $hook) use ($subscriptions) {
                $subscription = $subscriptions ? $subscriptions->firstWhere('hook', $hook) : null;

                return [
                    'hook'      => $hook,
                    'url'       => $subscription->url ?? null,
                    'connected' => (bool) $subscription,
                ];
            }, WebhookSubscription::ALL),
        ]);
    }

    protected function getExistingSubscriptions(): WebhookSubscriptionCollection
    {
        $url = $this->pdkWebhooksRepository->getHashedUrl();

        return $this->repository
            ->getAll()
            ->filter(static fn(WebhookSubscription $subscription) => Str::startsWith($subscription->url, $url));
    }
}
