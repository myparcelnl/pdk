<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Webhook\Repository\AbstractPdkWebhooksRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;

final class MockPdkWebhooksRepository extends AbstractPdkWebhooksRepository
{
    private const DEFAULT_SUBSCRIPTIONS = [
        [
            'id'   => 1,
            'hook' => WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED,
            'url'  => 'https://example.com',
        ],
        [
            'id'   => 2,
            'hook' => WebhookSubscription::SHOP_UPDATED,
            'url'  => 'https://example.com',
        ],
    ];

    /**
     * @var string
     */
    protected $hashedUrl = 'https://example.com/hook/1234567890abcdef';

    /**
     * @var array
     */
    protected $subscriptions;

    public function __construct(
        StorageInterface              $storage,
        WebhookSubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($storage, $subscriptionRepository);

        $this->subscriptions = new WebhookSubscriptionCollection(self::DEFAULT_SUBSCRIPTIONS);
    }

    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->subscriptions;
    }

    public function getHashedUrl(): ?string
    {
        return $this->hashedUrl;
    }

    public function remove(string $hook): void
    {
        $this->subscriptions = $this->subscriptions->where('hook', '!=', $hook);
    }

    public function store(WebhookSubscriptionCollection $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }

    public function storeHashedUrl(string $url): void
    {
        $this->hashedUrl = $url;
    }
}
