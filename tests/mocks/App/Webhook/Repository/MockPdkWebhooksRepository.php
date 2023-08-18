<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Repository;

use MyParcelNL\Pdk\Contract\MockServiceInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;

final class MockPdkWebhooksRepository extends AbstractPdkWebhooksRepository implements MockServiceInterface
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
     * @var WebhookSubscriptionCollection
     */
    protected $subscriptions;

    public function __construct(StorageInterface $storage, WebhookSubscriptionRepository $subscriptionRepository)
    {
        parent::__construct($storage, $subscriptionRepository);

        $this->reset();
    }

    //    /**
    //     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
    //     * @param  \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $subscriptionRepository
    //     */
    //    public function __construct(StorageInterface $storage, WebhookSubscriptionRepository $subscriptionRepository)
    //    {
    //        parent::__construct($storage, $subscriptionRepository);
    //
    //    }

    /**
     * @return \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
     */
    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->subscriptions;
    }

    /**
     * @return null|string
     */
    public function getHashedUrl(): ?string
    {
        return $this->hashedUrl;
    }

    /**
     * @param  string $hook
     *
     * @return void
     */
    public function remove(string $hook): void
    {
        $this->subscriptions = $this->subscriptions->where('hook', '!=', $hook);
    }

    /**
     * @param  null|array $subscriptions
     *
     * @return void
     */
    public function reset(?array $subscriptions = self::DEFAULT_SUBSCRIPTIONS): void
    {
        $this->subscriptions = new WebhookSubscriptionCollection($subscriptions);
    }

    /**
     * @param  \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection $subscriptions
     *
     * @return void
     */
    public function store(WebhookSubscriptionCollection $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * @param  string $url
     *
     * @return void
     */
    public function storeHashedUrl(string $url): void
    {
        $this->hashedUrl = $url;
    }
}
