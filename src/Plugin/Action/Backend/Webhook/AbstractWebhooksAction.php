<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Webhook;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Plugin\Contract\ActionInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractWebhooksAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhooksRepositoryInterface
     */
    protected $pdkWebhooksRepository;

    /**
     * @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository
     */
    protected $repository;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhookServiceInterface
     */
    protected $webhookActions;

    /**
     * @param  \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository       $repository
     * @param  \MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhooksRepositoryInterface $pdkWebhooksRepository
     * @param  \MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhookServiceInterface     $pdkWebhookActions
     */
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
     *
     * @return \MyParcelNL\Pdk\Api\Response\JsonResponse
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

    /**
     * @return \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection|\MyParcelNL\Pdk\Webhook\Model\WebhookSubscription[]
     */
    protected function getExistingSubscriptions()
    {
        $url = $this->pdkWebhooksRepository->getHashedUrl();

        return $this->repository
            ->getAll()
            ->filter(static function (WebhookSubscription $subscription) use ($url) {
                return Str::startsWith($subscription->url, $url);
            });
    }
}
