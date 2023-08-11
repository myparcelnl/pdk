<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Repository;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;

abstract class AbstractPdkWebhooksRepository extends Repository implements PdkWebhooksRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository
     */
    protected $apiRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
     * @param  \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $subscriptionRepository
     */
    public function __construct(StorageInterface $storage, WebhookSubscriptionRepository $subscriptionRepository)
    {
        parent::__construct($storage);
        $this->apiRepository = $subscriptionRepository;
    }

    /**
     * @param  string $hook
     *
     * @return null|\MyParcelNL\Pdk\Webhook\Model\WebhookSubscription
     */
    public function get(string $hook): ?WebhookSubscription
    {
        return $this->getAll()
            ->firstWhere('hook', $hook);
    }

    /**
     * @param  string $hook
     *
     * @return bool
     */
    public function has(string $hook): bool
    {
        return null !== $this->get($hook);
    }
}
